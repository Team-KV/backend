<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Returns response with categories in JSON
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return response()->json(Category::getAllCategories());
    }

    /**
     * Returns response with created category in JSON
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create(Request $request): Response|JsonResponse
    {
        $params = $request->validate([
            'name' => ['required', 'string'],
            'color' => ['string', 'nullable']
        ]);

        try {
            $category = Category::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.categoryCreateError')], 409);
        }

        return response()->json(['Category' => $category]);
    }

    /**
     * Returns response with specific category in JSON
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $category = Category::getCategoryByID($id);
        if ($category == null) {
            return response(['message' => trans('messages.categoryDoesntExistError')], 404);
        }

        return response()->json(['Category' => $category]);
    }

    /**
     * Returns response with updated category in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $category = Category::getCategoryByID($id);
        if ($category == null) {
            return response(['message' => trans('messages.categoryDoesntExistError')], 404);
        }

        $params = $request->validate([
            'name' => ['required', 'string'],
            'color' => ['string', 'nullable']
        ]);

        if (Category::updateCategory($category, $params)) {
            return response()->json(['Category' => $category]);
        } else {
            return response(['message' => trans('messages.categoryUpdateError')], 409);
        }
    }

    /**
     * Returns response after success delete
     *
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $category = Category::getCategoryByID($id);
        if ($category == null) {
            return response(['message' => trans('messages.categoryDoesntExistError')], 404);
        }

        //TODO: Check exercises in category

        $category->delete();

        return response('', 204);
    }
}
