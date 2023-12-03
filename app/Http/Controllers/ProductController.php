<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Product;
use Stripe\StripeClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;
use Stripe\Webhook;
use UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected StripeClient|null $stripe = null;

    public function __construct() {
        $sk = config("stripe.sk");
        $this->stripe = new StripeClient($sk);
    }

    public function index(Request $request) {
        $products = Product::all();
        return view("product.index", compact("products"));
    }

    public function checkout(Request $request) {
        $products = Product::all();
        $lineItems = [];
        $totalPrice = 0;
        foreach($products as $product) {
            $totalPrice += $product->price;
            $lineItems[] = [
                "price_data" => [
                    "currency" => "gbp",
                    "product_data" => [
                        "name" => $product->name,
                        "images" => [$product->image],
                    ],
                    "unit_amount" => $product->price * 100,
                ],
                "quantity" => 1
            ];
        }
        $session = $this->stripe
            ->checkout
            ->sessions
            ->create([
                "line_items" => $lineItems,
                "mode" => "payment",
                "success_url" => route("checkout.success", [], true)."?session_id={CHECKOUT_SESSION_ID}",
                "cancel_url" => route("checkout.cancel", [], true),
                "customer_creation" => "always",
            ]);

        $order = new Order();
        $order->status = "unpaid";
        $order->total_price = $totalPrice;
        $order->session_id = $session->id;
        $order->save();

        return redirect()
            ->away($session->url);
    }

    public function success(Request $request) {
        $sessionId = $request->get("session_id");
        $customer = null;
        try {
            $session = $this->stripe
                ->checkout
                ->sessions
                ->retrieve($sessionId);
            if (!$session) {
                throw new NotFoundHttpException();
            }
            $customer = $this->stripe
                ->customers
                ->retrieve($session->customer);

            $order = Order::where(
                "session_id",
                $session->id
            )
                ->where("status", "unpaid")
                ->first();
            if (!$order) {
                throw new NotFoundHttpException();
            }
            $order->status = "paid";
            $order->save();
            return view(
                "product.checkout-success",
                compact("customer")
            );
        } catch (Exception $e) {
            throw new NotFoundHttpException();
        }
    }

    public function cancel() {

    }

    public function webhook() {
        $endpoint_secret = config("stripe.webhook");

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(UnexpectedValueException $e) {
            // Invalid payload
            return response("", 400);
        } catch(SignatureVerificationException $e) {
            // Invalid signature
            return response("", 400);
        }
        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $sessionId = $session->id;
                Log::debug($session);
                $order = Order::where(
                    "session_id",
                    $sessionId
                )
                    ->first();
                if ($order && $order->status === "unpaid") {
                    $order->status = "paid";
                    $order->save();
                    // Send email to customer.
                }
            // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return response("");
    }
}
