<?php 

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id ,
            'total_price' => $this->total_price,
            'status' => OrderStatus::tryFrom($this->status)->name(),
            'products' => ProductResource::collection($this->products) 
        ];
    }
}
