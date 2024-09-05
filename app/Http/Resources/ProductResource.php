<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this-> id ,
            'name' => $this->name ,
            'quantity' => $this->quantity ,
            'price' => $this->price ,
            'description' => $this->description ,
            'category' => new CategoryResource($this->category),
            'images' => $this->images->map(function($image) {
                return asset('storage/images/' . $image->name);
            }),
        ] ;
    }
}
