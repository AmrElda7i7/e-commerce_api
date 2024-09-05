<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @group User management
 *
 * User resource representation.
 *
 * @response {
 *   "id": 1,
 *   "name": "John Doe",
 *   "email": "johndoe@example.com",
 *   "is_verified": false,
 *   "token": "your_generated_token_here",
 *   "role": "user"
 * }
 */
class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_verified' => $this->is_verified==1 ? true : false,
            'token' => $this->token,
            'role' => $this->getRoleNames()->first() ?? null
        ];
    }
}