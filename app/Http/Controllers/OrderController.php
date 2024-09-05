<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends Controller
{
    use ApiHandlerTrait;
    private $orderRequest ;
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Create a new order.
     *
     * @group Orders
     * @authenticated
     * 
     * @bodyParam user_id integer required The ID of the user placing the order. Example: 1
     * @bodyParam products array required The list of products in the order. Example: [{"id":1,"quantity":2},{"id":2,"quantity":1}]
     * @bodyParam status string required The status of the order, either 'cash_on_delivery' or 'online_payment'. Example: cash_on_delivery
     * 
     * @response 200 {
     *  "status": "success",
     *  "message": "Order has been created successfully",
     *  "data": {
     *    "id": 1,
     *    "order_number": "ORD-12345",
     *    "total_price": 100.50,
     *    "status": "pending",
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
     * @response 400 {
     *  "status": "error",
     *  "message": "Insufficient stock",
     *  "errors": ["insufficient stock"]
     * }
     * 
     * @response 404 {
     *  "status": "error",
     *  "message": "Product not found",
     *  "errors": ["product not found"]
     * }
     * 
     * @response 422 {
     *  "status": "error",
     *  "message": "The given data was invalid.",
     *  "errors": {
     *    "user_id": ["User ID is required."],
     *    "products": ["At least one product is required."],
     *    "status": ["Status is required."]
     *  }
     * }
     */
    public function store(OrderRequest $request)
    {
        $order_num = $this->orderService->generateOrderNumber();
        $this->orderRequest = $request->validated();

        if ($request->status === 'cash_on_delivery') {
            try {
                $order = $this->orderService->createOrder('cash_on_delivery', $this->orderRequest, $order_num);
                return $this->successResponse('Order has been created successfully', new OrderResource($order));
            } catch (InsufficientStockException $e) {
                return $this->errorResponse($e->getMessage(), 400, ['insufficient stock']);
            } catch (NotFoundHttpException $e) {
                return $this->errorResponse($e->getMessage(), 404, ['product not found ']);
            }
        } else {
            $total_price = $this->orderService->calcTotalPrice($request['products']);
            $order_num = $this->orderService->generateOrderNumber();
            $order = $this->orderService->createOrder('online_payment', $this->orderRequest, $order_num);
            $payment = new PaymentService($total_price);
            $output = $payment->pay();
            return $this->successResponse('Order is ready to be paid', [
                'url' => $output
            ]);
        }
    }

    /**
     * Handle payment callback.
     *
     * @group Orders
     * @authenticated
     * 
     * @response 200 {
     *  "status": "success",
     *  "message": "Order has been created successfully",
     *  "data": {
     *    "id": 1,
     *    "order_number": "ORD-12345",
     *    "total_price": 100.50,
     *    "status": "pending",
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
     * @response 400 {
     *  "status": "error",
     *  "message": "Something wrong with payment",
     *  "errors": ["something wrong"]
     * }
     */
    public function callback(Request $request)
    {
        $data = $request->all();
        ksort($data);
        $hmac = $data['hmac'];
        $array = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];
        $connectedString = '';
        foreach ($data as $key => $element) {
            if (in_array($key, $array)) {
                $connectedString .= $element;
            }
        }
        $secret = env('PAYMOB_HMAC');
        $hashed = hash_hmac('sha512', $connectedString, $secret);
        $order = Order::latest('id')->first();
        if ($hashed == $hmac) {
            return $this->successResponse('Order has been created successfully', new OrderResource($order));
        }
        $order->delete();
        return $this->errorResponse('Something wrong with payment', 400, ['something wrong']);
    }
    
 /**
     * Get orders for the user.
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
     * */
    
    public function getOrdersForUser() 
    {
        $userId = Auth()->user()->id ;
        $orders = Order::with('products')->where('user_id' ,$userId)->get() ;
        return $this->successResponse('Orders have been retrieved successfully', OrderResource::collection($orders));
    }
}
