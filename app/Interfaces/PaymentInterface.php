<?php

namespace App\Interfaces;
use Illuminate\Http\Request;
use App\Services\OrderService ;
interface PaymentInterface
{
    public function pay()  ;
}
