<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PartController extends Controller
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
        $categories = Part::with('post')->get();
        return $this->successResponse($categories, 'messages', 'partsـretrievedـsuccessfully');
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request, [
            'post_id' => [
                'required',
                Rule::exists('posts', 'id')->whereNull('deleted_at'),
            ],
            'title' => 'required|string',
            'description' => 'nullable' ,
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('parts', 'public');
        }

        $part = Part::create($data);

        return $this->successResponse($part, 'messages', 'created');
    }

    public function update(Request $request, $id)
    {
        $part = Part::find($id);
        if (!$part) {
            return $this->errorResponse(__('not_found'), 'messages', 404);
        }
        $data = $this->validateRequest($request, [
            'title' => 'required|string',
            'description' => 'nullable',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            if ($part->image) {
                Storage::disk('public')->delete($part->image);
            }
            $data['image'] = 'storage/' . $request->file('image')->store('parts', 'public');
        }

        $part->update($data);

        return $this->successResponse($part, 'messages', 'updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $part = Part::find($id);

        if (!$part) {
            return $this->errorResponse(__('not_found'), 'messages', 404);
        }

        $part->delete();

        return $this->successResponse([], 'messages', 'deleted');
    }
}
