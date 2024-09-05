<?php

namespace App\Repositories;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function decreaseQuantity($products, $request)
    {
        // Create a map of product quantities by product ID
        $productQuantities = collect($request['products'])->keyBy('id');

        foreach ($products as $product) {
            $productId = $product->id;

            // Check if product ID is in the request data
            if (isset($productQuantities[$productId])) {
                $quantityToDecrease = $productQuantities[$productId]['quantity'];

                // Check if there's enough stock
                if ($product->quantity >= $quantityToDecrease) {
                    $product->quantity -= $quantityToDecrease;
                    $product->save();
                } else {
                    // Handle case where there's insufficient stock
                    throw new InsufficientStockException("Insufficient stock for product: " . $product->name);
                }
            } else {
                // Handle case where product ID from products list is not found in request
                throw new NotFoundHttpException("Product ID: $productId not found in request data");
            }
        }
    }
      
}
