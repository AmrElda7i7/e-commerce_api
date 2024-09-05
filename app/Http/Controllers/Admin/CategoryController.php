<?php
namespace App\Http\Controllers\Admin;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiHandlerTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

/**
 * @group categories management
 *
 * APIs for managing categories.
 */

class CategoryController extends Controller implements HasMiddleware
{
    use ApiHandlerTrait;


    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_category', only: ['store']),
            new Middleware('permission:update_category', only: ['update']),
            new Middleware('permission:delete_category', only: ['destroy']),
            new Middleware('permission:show_categories', only: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the categories.
     *
     * @authenticated
     * @response 200 {
     *  "status": true,
     *  "message": "Categories retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "Electronics",
     *      "image": "path/to/image.jpg"
     *    }
     *  ]
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     */
    public function index()
    {
        $categories = Category::all();
        return $this->successResponse('Categories retrieved successfully', CategoryResource::collection($categories));
    }

    /**
     * Store a newly created category in storage.
     *
     * @authenticated
     * @param  \App\Http\Requests\CategoryRequest  $request
     * @response 201 {
     *  "status": true,
     *  "message": "Category created successfully",
     *  "data": {
     *    "id": 2,
     *    "name": "Clothing",
     *    "image": "path/to/image.jpg"
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "name": ["The name field is required."],
     *      "image": ["The image must be a file of type: jpeg, png, jpg, gif, svg."]
     *    }
     *  }
     * }
     */
    public function store(CategoryRequest $request)
    {
        $category = null;
        DB::transaction(function () use ($request, &$category) {
            $imageName = storeImage($request->image);

            $category = Category::create([
                'name' => $request->name,
                'image_name' => $imageName,
            ]);
        });

        return $this->successResponse('Category created successfully', new CategoryResource($category), 201);
    }


    /**
     * Display the specified category.
     *
     * @authenticated
     * @param  int  $id
     * @response 200 {
     *  "status": true,
     *  "message": "Category retrieved successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "Electronics",
     *    "image": "path/to/image.jpg"
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     * @response 404 {
     *  "status": false,
     *  "message": "No resource was found",
     *  "data": null
     * }
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return $this->successResponse('Category retrieved successfully', new CategoryResource($category));
    }

    /**
     * Update the specified category in storage.
     *
     * @authenticated
     * @param  \App\Http\Requests\CategoryRequest  $request
     * @param  \App\Models\Category  $category
     * @response 200 {
     *  "status": true,
     *  "message": "Category updated successfully",
     *  "data": {
     *    "id": 1,
     *    "name": "Updated Name",
     *    "image": "path/to/image.jpg"
     *  }
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     * @response 404 {
     *  "status": false,
     *  "message": "Category not found.",
     *  "data": null
     * }
     * @response 422 {
     *  "status": false,
     *  "message": "The given data was invalid.",
     *  "data": {
     *    "errors": {
     *      "name": ["The name field is required."],
     *      "image": ["The image must be a file of type: jpeg, png, jpg, gif, svg."]
     *    }
     *  }
     * }
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->name = $request->name;
        if ($request->hasFile('image')) {
            $imagePath = 'images/' . $category->image_name;
            Storage::disk('public')->delete($imagePath);
            $imageName = storeImage($request->image);
            $category->image_name = $imageName;
        }

        $category->update();
        return $this->successResponse('Category updated successfully', new CategoryResource($category));
    }



    /**
     * Remove the specified category from storage.
     *
     * @authenticated
     * @param  \App\Models\Category  $category
     * @response 200 {
     *  "status": true,
     *  "message": "Category deleted successfully",
     *  "data": null
     * }
     * @response 401 {
     *  "status": false,
     *  "message": "Unauthenticated.",
     *  "data": null
     * }
     * @response 403 {
     *  "status": false,
     *  "message": "User does not have the right permissions.",
     *  "data": null
     * }
     * @response 404 {
     *  "status": false,
     *  "message": "Category not found.",
     *  "data": null
     * }
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $imagePath = 'images/' . $category->image_name;
        Storage::disk('public')->delete($imagePath);
        $category->delete();
        return $this->successResponse('Category deleted successfully');
    }

}
