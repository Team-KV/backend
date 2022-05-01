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
        return $this->sendData(Category::getAllCategories());
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
            return $this->sendInternalError('messages.categoryCreateError');
        }

        return $this->sendData(['Category' => $category]);
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
            return $this->sendNotFound('messages.categoryDoesntExistError');
        }

        return $this->sendData(['Category' => $category]);
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
            return $this->sendNotFound('messages.categoryDoesntExistError');
        }

        $params = $request->validate([
            'name' => ['required', 'string'],
            'color' => ['string', 'nullable']
        ]);

        if (Category::updateCategory($category, $params)) {
            return $this->sendData(['Category' => $category]);
        } else {
            return $this->sendInternalError('messages.categoryUpdateError');
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
        $category = Category::getCategoryWithAllByID($id);
        if ($category == null) {
            return $this->sendNotFound('messages.categoryDoesntExistError');
        }

        if(count($category->exercises) != 0) {
            return $this->sendConflict('messages.categoryHasExercisesError');
        }

        $category->delete();

        return $this->sendNoContent();
    }
}
