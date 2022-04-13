<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TagController extends Controller
{
    /**
     * Returns response with collection of tags in JSON
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return response()->json(Tag::getListOfTags());
    }

    /**
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
            $tag = Tag::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.tagCreateError')], 409);
        }

        return response()->json(['Tag' => $tag]);
    }

    /**
     * Returns response with tag by ID in JSON
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $tag = Tag::getTagByID($id);
        if($tag == null) {
            return response(['message' => trans('messages.tagDoesntExistError')], 404);
        }

        return response()->json(['Tag' => $tag]);
    }

    /**
     * Returns response with updated tag in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $tag = Tag::getTagByID($id);
        if($tag == null) {
            return response(['message' => trans('messages.tagDoesntExistError')], 404);
        }

        $params = $request->validate([
            'name' => ['required', 'string'],
            'color' => ['string', 'nullable']
        ]);

        if(Tag::updateTag($tag, $params)) {
            return response()->json(['Tag' => $tag]);
        }
        else {
            return response(['message' => trans('messages.tagUpdateError')], 409);
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
        $tag = Tag::getTagByID($id);
        if($tag == null) {
            return response(['message' => trans('messages.tagDoesntExistError')], 404);
        }

        $tag->clients()->detach();

        $tag->delete();

        return response('', 204);
    }
}
