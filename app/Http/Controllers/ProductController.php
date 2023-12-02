<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Product;
use Stripe\StripeClient;

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
                "success_url" => route("checkout.success", [], true),
                "cancel_url" => route("checkout.cancel", [], true),
            ]);

        $order = new Order();
        $order->status = "unpaid";
        $order->total_price = $totalPrice;
        $order->session_id = $session->id;
        $order->save();

        return redirect()
            ->away($session->url);
    }

    public function success() {

    }

    public function cancel() {

    }
}
