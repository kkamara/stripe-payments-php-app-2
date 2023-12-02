<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
    </head>
    <body class="antialiased">
        <div style="display: flex; gap: 2rem">
            @foreach($products as $product)
                <img
                    src="{{$product->image}}"
                    alt=""
                    style="max-width: 100px"
                />
                <div class="flex: 1">
                    <h5>£{{$product->name}}</h5>
                </div>
                <p>{{$product->price}}</p>
            @endforeach
        </div>
        <p>
            <button>Checkout</button>
        </p>
    </body>
</html>
