<?php

namespace App\Services;

use App\Interfaces\PaymentInterface;
use App\Http\Controllers\Controller;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\OrderService ;
use App\Http\Resources\OrderResource;

class PaymentService implements PaymentInterface
{
    use ApiHandlerTrait;
    private $total_price ;

    public function __construct($total_price )
    {
        $this->total_price = $total_price ;
      
    }
    public function pay()
    {
        $token = $this->getToken();
        $order = $this->createOrder($token);
        $paymentToken = $this->getPaymentToken($order, $token);
       return ('https://portal.weaccept.co/api/acceptance/iframes/' . env('PAYMOB_IFRAME_ID') . '?payment_token=' . $paymentToken);
    }
    public function getToken()
    {
        $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => env('PAYMOB_API_KEY')
        ]);
        return $response->json()['token'];
    }
    public function createOrder($token)
    {
        $items = [
            [ "name"=> "ASC1515",
                "amount_cents"=> "500000",
                "description"=> "Smart Watch",
                "quantity"=> "1"
            ],
            [
                "name"=> "ERT6565",
                "amount_cents"=> "200000",
                "description"=> "Power Bank",
                "quantity"=> "1"
            ]
        ];
        $data = [
            "auth_token" => $token,
            "delivery_needed" => "false",
            "amount_cents" => $this->total_price *100,
            "currency" => "EGP",
            "items"=> $items,

        ];
        $response = Http::post('https://accept.paymob.com/api/ecommerce/orders', $data);
        return $response->object();
    }

    public function getPaymentToken($order, $token)
    {
        $billingData = [
            "apartment" => "803",
            "email" => "claudette09@exa.com",
            "floor" => "42",
            "first_name" => "Clifford",
            "street" => "Ethan Land",
            "building" => "8028",
            "phone_number" => "+86(8)9135210487",
            "shipping_method" => "PKG",
            "postal_code" => "01898",
            "city" => "Jaskolskiburgh",
            "country" => "CR",
            "last_name" => "Nicolas",
            "state" => "Utah"
        ];
        $data = [
            "auth_token" => $token,
            "amount_cents" => $this->total_price * 100,
            "expiration" => 3600,
            "order_id" => $order->id,
            "billing_data" => $billingData,
            "currency" => "EGP",
            "integration_id" => env('PAYMOB_INTEGRATION_ID')
        ];
        $response = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', $data);
        return ($response->json()['token']);
    }

}
