<?php 
// database/factories/UserFactory.php

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // use bcrypt to hash the password
            'is_verified' => 1, // replace email_verified_at with is_verified
            'remember_token' => Str::random(10),
        ];
    }
}
