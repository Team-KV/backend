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
        return $this->sendData(Tag::getListOfTags());
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
            return $this->sendInternalError('messages.tagCreateError');
        }

        return $this->sendData(['Tag' => $tag]);
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
            return $this->sendNotFound('messages.tagDoesntExistError');
        }

        return $this->sendData(['Tag' => $tag]);
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
            return $this->sendNotFound('messages.tagDoesntExistError');
        }

        $params = $request->validate([
            'name' => ['required', 'string'],
            'color' => ['string', 'nullable']
        ]);

        if(Tag::updateTag($tag, $params)) {
            return $this->sendData(['Tag' => $tag]);
        }
        else {
            return $this->sendInternalError('messages.tagUpdateError');
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
            return $this->sendNotFound('messages.tagDoesntExistError');
        }

        $tag->clients()->detach();

        $tag->delete();

        return $this->sendNoContent();
    }
}
