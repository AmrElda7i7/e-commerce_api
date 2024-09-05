<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['total_price' , 'order_number' ,'user_id' ,'status'] ;
    public function products() 
    {
        return $this->belongsToMany(Product::class ,'order_products');
    }
}
