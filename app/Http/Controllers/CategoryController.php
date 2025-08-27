<?php

namespace App\Http\Controllers;
use App\Models\Category;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;


class CategoryController extends Controller
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

        $categories = Category::get();
        return $this->successResponse($categories, 'messages', 'categoriesـretrievedـsuccessfully');
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request, [
            'title' => 'required|string|max:255',
        ]);


        $category = Category::create($data);

        return $this->successResponse($category, 'messages', 'created');
    }
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse(__('not_found'), 'messages', 404);
        }

        $data = $this->validateRequest($request, [
            'title' => 'sometimes|string',

        ]);

        $category->update($data);

        return $this->successResponse($category, 'messages', 'updated');
    }
     public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return $this->errorResponse(__('not_found'), 'messages', 404);
        }
        $category->delete();

        return $this->successResponse([], 'messages', 'deleted');
    }
}
