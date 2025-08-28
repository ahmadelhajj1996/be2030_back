<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Traits\ApiResponseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use ApiResponseTrait;
    private function validateRequest(Request $request, array $rules)
    {
        try {
            return $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new HttpResponseException(
                $this->errorResponse(__('messages.validation_failed'), 'messages', 422, [
                    'errors' => $e->errors(),
                ])
            );
        }
    }

    public function index(Request $request)
    {
        $categories = Post::get();
        return $this->successResponse($categories, 'messages', 'postsـretrievedـsuccessfully');
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request, [
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
            'title' => 'required|string',

            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('posts', 'public');
        }

        $post = Post::create($data);

        return $this->successResponse($post, 'messages', 'created');
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->errorResponse(__('not_found'), 'messages', 404);
        }
        $data = $this->validateRequest($request, [
            'title' => 'required|string',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = 'storage/' . $request->file('image')->store('subcategories', 'public');
        }

        $post->update($data);

        return $this->successResponse($post, 'messages', 'updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->errorResponse(__('not_found'), 'messages', 404);
        }

        $post->delete();

        return $this->successResponse([], 'messages', 'deleted');
    }

}
