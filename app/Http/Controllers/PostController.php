<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use ApiResponseTrait;

    /**
     * Validation rules for store and update methods
     */
    private function getValidationRules($isUpdate = false)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ];

        if ($isUpdate) {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    /**
     * Custom validation method with consistent error handling
     */
    private function validateRequest(Request $request, array $rules)
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new HttpResponseException(
                $this->errorResponse(__('messages.validation_failed'), 'messages', 422, [
                    'errors' => $validator->errors(),
                ])
            );
        }

        return $validator->validated();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = Post::with('parts')->get();
        return $this->successResponse($posts, 'messages', 'posts_retrieved_successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request
        $validatedData = $this->validateRequest($request, $this->getValidationRules());

        try {
            // Handle image upload
            $imagePath = $this->handleImageUpload($request->file('image'));

            // Create post
            $post = Post::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'category_id' => $validatedData['category_id'],
                'image' => $imagePath,
            ]);

            return $this->successResponse(
                $post->load('category'),
                'messages',
                'post_created_successfully',
                201
            );

        } catch (\Exception $e) {
            // Clean up image if post creation fails
            if (isset($imagePath)) {
                $this->cleanupImage($imagePath);
            }

            return $this->errorResponse(
                __('messages.operation_failed'),
                'messages',
                500,
                ['error' => config('app.debug') ? $e->getMessage() : 'Internal server error']
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->errorResponse(__('messages.not_found'), 'messages', 404);
        }

        // Validate request
        $validatedData = $this->validateRequest($request, $this->getValidationRules(true));

        try {
            // Handle image upload if a new image is provided
            if ($request->hasFile('image')) {
                // Delete old image
                $this->cleanupImage($post->image);

                // Upload new image
                $validatedData['image'] = $this->handleImageUpload($request->file('image'));
            } else {
                // Keep the existing image if no new image is uploaded
                $validatedData['image'] = $post->image;
            }

            // Update post
            $post->update($validatedData);

            return $this->successResponse(
                $post->load('category'),
                'messages',
                'post_updated_successfully'
            );

        } catch (\Exception $e) {
            // Clean up new image if update fails
            if ($request->hasFile('image') && isset($validatedData['image'])) {
                $this->cleanupImage($validatedData['image']);
            }

            return $this->errorResponse(
                __('messages.operation_failed'),
                'messages',
                500,
                ['error' => config('app.debug') ? $e->getMessage() : 'Internal server error']
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->errorResponse(__('messages.not_found'), 'messages', 404);
        }

        try {
            // Delete associated image
            $this->cleanupImage($post->image);

            // Delete post
            $post->delete();

            return $this->successResponse([], 'messages', 'post_deleted_successfully');

        } catch (\Exception $e) {
            return $this->errorResponse(
                __('messages.operation_failed'),
                'messages',
                500,
                ['error' => config('app.debug') ? $e->getMessage() : 'Internal server error']
            );
        }
    }

    /**
     * Handle image upload with validation and storage
     */
    protected function handleImageUpload($imageFile): string
    {
        if (!$imageFile || !$imageFile->isValid()) {
            throw new \Exception('Invalid image file');
        }

        // Generate unique filename with original extension
        $extension = $imageFile->getClientOriginalExtension();
        $filename = 'posts/' . uniqid() . '_' . time() . '.' . $extension;

        // Store image in storage (public disk)
        $path = $imageFile->storeAs('public', $filename);

        // Return path without 'public/' prefix for database storage
        return $filename;
    }

    /**
     * Clean up image file from storage
     */
    protected function cleanupImage($imagePath): void
    {
        if ($imagePath && Storage::exists('public/' . $imagePath)) {
            Storage::delete('public/' . $imagePath);
        }
    }
}
