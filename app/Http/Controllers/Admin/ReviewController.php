<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReviewController extends Controller implements HasMiddleware
{
    use ApiHandlerTrait;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:delete_review', only: ['destroy']),
            new Middleware('permission:show_reviews', only: ['index', 'show']),
        ];
    }

    /**
     * Get a list of reviews.
     * @authenticated
     * @group Reviews
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "Reviews have been retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "rating": 5,
     *          "content": "Great product!",
     *          "product": {
     *              "id": 1,
     *              "name": "Product Name",
     *              "price": 100
     *          },
     *          "user": {
     *             "id": 1,
     *             "name": "John Doe",
     *             "email": "john.doe@example.com",
     *             "role": "admin"
     *          },
     *      },
     *  ]
     * }
     *  @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     */
    public function index() 
    {
        $reviews = Review::with('product', 'user')->get();
        return $this->successResponse('Reviews have been retrieved successfully', ReviewResource::collection($reviews));
    }

    /**
     * Get a specific review.
     * @authenticated
     * @group Reviews
     * @urlParam id int required The ID of the review. Example: 1
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
     *  @response 404 {
     *  "status": false,
     *  "message": "no resource was found",
     *  "errors": []
     * }
     *  @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     */
    public function show($id) 
    {
        $review = Review::with('product', 'user')->findOrFail($id);
        return $this->successResponse('review has been retrieved successfully', new ReviewResource($review));
    }

    /**
     * Delete a specific review.
     * @authenticated
     * @group Reviews
     * @urlParam id int required The ID of the review to delete. Example: 1
     * 
     * @response 200 {
     *  "status": true,
     *  "message": "review deleted successfully"
     * }
     * 
     *  @response 404 {
     *  "status": false,
     *  "message": "no resource was found",
     *  "errors": []
     * }
     *  @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     */
    public function destroy($id) 
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return $this->successResponse('review deleted successfully');
    }
}
