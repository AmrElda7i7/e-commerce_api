<?php
namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Models\Product;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderService
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    /**
     * Generate a unique order number.
     *
     * @return string
     */
    public function generateOrderNumber(): string
    {
        $timestamp = time(); // Seconds timestamp
        $randomNumber = random_int(0, 99999); // 5-digit random number

        return 'ORD-' . substr($timestamp, -3) . str_pad($randomNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new order.
     *
     * @param string $status
     * @param array $request
     * @param string $order_num
     * @return Order
     */
    public function createOrder(string $status, array $request, string $order_num): Order
    {

        // Retrieve the product IDs from the request
        $productIds = collect($request['products'])->pluck('id');

        // Get the products from the database
        $products = Product::whereIn('id', $productIds)->get();
        $total_price = $this->calcTotalPrice($request['products']);
        $this->orderRepository->decreaseQuantity($products, $request);
        $order = Order::create([
            'user_id' => $request['user_id'],
            'status' => OrderStatus::search($status)->value,
            'order_number' => $order_num,
            'total_price' => $total_price,
        ]);

        foreach ($request['products'] as $productData) {
            DB::table('order_products')->insert([
                'order_id' => $order->id,
                'product_id' => $productData['id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Uncomment if you need to decrease product quantities


        }

        return $order;
    }
 /**
     * Calculate the total price of products.
     *
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @return float
     */

     public function calcTotalPrice($request): float
     {
         $total_price = 0;
         foreach ($request as $productData) {
             $product =Product::findOrFail($productData['id']) ;
             $total_price += $productData['quantity'] * $product->price ;
         }
         return $total_price;
     }




}
/*
repositories pattern
two exceptions
exception for payment

*/