<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiHandlerTrait;

    /**
     * @group Reviews
     *
     * APIs for managing reviews.
     */

    /**
     * Store a new review.
     * 
     * @group Reviews
     * @authenticated
     * @bodyParam product_id int required The ID of the product being reviewed. Example: 1
     * @bodyParam rating int required The rating given by the user. Example: 5
     * @bodyParam content string The content of the review. Example: "Great product!"
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "review has been created successfully",
     *  "data": {
     *      "id": 1,
     *      "rating": 5,
     *      "content": "Great product!",
     *      "product": {
     *          "id": 1,
     *          "name": "Product Name",
     *          "price": 100
     *      },
     *      "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john.doe@example.com",
     *         "role": "admin"
     *      },
     *  }
     * }
     * 
     * @response 422 {
     *  "status": false,
     *  "message": "Validation error",
     *  "errors": {
     *      "product_id": ["The product id field is required."],
     *      "rating": ["The rating field is required."],
     *      "content": ["The content field is required."]
     *  }
     * }
     */
    public function store(ReviewRequest $request) 
    {
        $review = Review::create([
            'user_id' => auth()->user()->id,
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'content' => $request->content
        ]);

        return $this->successResponse('review has been created successfully', new ReviewResource($review));
    }

    /**
     * Get a review for a specific product.
     * 
     * @group Reviews
     * @authenticated
     * @urlParam id int required The ID of the product. Example: 1
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "review has been retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "rating": 5,
     *      "content": "Great product!",
     *      "product": {
     *          "id": 1,
     *          "name": "Product Name",
     *          "price": 100
     *      },
     *      "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john.doe@example.com",
     *         "role": "admin"
     *      },
     *  }
     * }
     * 
     * @response 404 {
     *  "status": false,
     *  "message": "no resource was found",
     *  "errors": []
     * }
     */
    public function getReviewForProduct($id) 
    {
        $review = Review::with('product', 'user')->where('product_id', $id)->firstOrFail();
        return $this->successResponse('review has been retrieved successfully', new ReviewResource($review));
    }
}
