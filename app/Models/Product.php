<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name' , 'price' , 'quantity' , 'description' ] ;
    public function images () 
    {
        return $this->hasMany(ProductImages::class) ;
    }
    public function category () 
    {
        return $this->belongsTo(Category::class) ;
    }
    public function orders() 
    {
        return $this->belongsToMany(Order::class,'order_products') ;
    }
    public function reviews()
    {
        return $this->hasMany(Review::class) ;
    }
}
