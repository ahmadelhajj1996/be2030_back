<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Traits\ApiResponseTrait;

use Illuminate\Http\Request;

class CategoryController extends Controller
{

    use ApiResponseTrait;

    public function index(Request $request)
    {

        $categories = Category::get();
        return $this->successResponse($categories, 'messages', 'categoriesـretrievedـsuccessfully');
    }
    
}
