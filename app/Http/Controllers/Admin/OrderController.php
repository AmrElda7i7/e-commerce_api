<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Traits\ApiHandlerTrait;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;

class OrderController extends Controller implements HasMiddleware
{
    /**
 * @group orders management
 *
 * APIs for managing orders.
 */
    use ApiHandlerTrait ;
    public static function middleware(): array
    {
        return [
            // new Middleware('permission:show_orders', only: ['index', 'show']),
        ];
    }

    /**
     * Get all orders.
     *
     * @group Orders
     * @authenticated
     *
     * @response 200 {
     *  "status": "true",
     *  "message": "Orders have been retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "user_id": 1,
     *      "status": "online_payment",
     *      "order_number": "ORD-12345",
     *      "total_price": 100.50,
     *      "products": [
     *        {
     *          "id": 1,
     *          "name": "Product 1",
     *          "price": 50.25
     *        },
     *        {
     *          "id": 2,
     *          "name": "Product 2",
     *          "price": 50.25
     *        }
     *      ]
     *    }
     *  ]
     * }
     *
    *@response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     *
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     */
    public function index()
    {
        $orders = Order::with('products')->get();
        return $this->successResponse('Orders have been retrieved successfully', OrderResource::collection($orders));
    }

    /**
     * Get a specific order by ID.
     *
     * @group Orders
     * @authenticated
     *
     * @urlParam id integer required The ID of the order. Example: 1
     *
      * @response 200 {
     *  "status": true,
     *  "message": "Order has been retrieved successfully",
     *  "data": {
     *    "id": 1,
     *    "user_id": 1,
     *    "status": "online_payment",
     *    "order_number": "ORD-12345",
     *    "total_price": 100.50,
     *    "products": [
     *      {
     *        "id": 1,
     *        "name": "Product 1",
     *        "price": 50.25
     *      },
     *      {
     *        "id": 2,
     *        "name": "Product 2",
     *        "price": 50.25
     *      }
     *    ]
     *  }
     * }
     *
        * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     *
    *@response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     *
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     */
    public function show($id)
    {
        $order = Order::with('products')->findOrFail($id);
        return $this->successResponse('Order has been retrieved successfully', new OrderResource($order));
    }
}
