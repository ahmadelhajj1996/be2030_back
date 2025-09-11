<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class LikeController extends Controller
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
        $categories = Like::get();
        return $this->successResponse($categories, 'messages', 'partsـretrievedـsuccessfully');
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request, [
            'post_id' => [
                'required',
                Rule::exists('posts', 'id')->whereNull('deleted_at'),
            ],
            'name' => 'required|string',
            'email' => 'required|email|string',
            'message' => 'required',
        ]);



        $part = Like::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'post_id' => $data['post_id'],
        ]);
        return $this->successResponse($part, 'messages', 'created');
    }
}
