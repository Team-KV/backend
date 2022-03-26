<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventTypeController extends Controller
{
    /**
     * Returns response with event types collection
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return response()->json(EventType::getListOfTypes());
    }

    /**
     * Creates unique event type and returns it in response
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create(Request $request): Response|JsonResponse
    {
        $params = $request->validate([
            'name' => ['required', 'string']
        ]);

        if(EventType::getEventTypeByName($params['name']) != null) {
            return response(['message' => trans('messages.eventTypeAlreadyExistsError')], 409);
        }

        try {
            $eventType = EventType::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.eventTypeCreateError')], 409);
        }

        return response()->json(['EventType' => $eventType]);
    }

    /**
     * Returns response with event type object
     *
     * @param $id
     * @return JsonResponse
     */
    public function detail($id): JsonResponse
    {
        return response()->json(['EventType' => EventType::getEventTypeByID($id)]);
    }

    /**
     * Returns response with updated event type
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $eventType = EventType::getEventTypeByID($id);
        if($eventType == null) {
            return response(['message' => trans('messages.eventTypeDoesntExistError')], 404);
        }

        $params = $request->validate([
            'name' => ['required', 'string']
        ]);

        if(EventType::getEventTypeByName($params['name']) != null) {
            return response(['message' => trans('messages.eventTypeAlreadyExistsError')], 409);
        }

        if(EventType::updateEventType($eventType, $params)) {
            return response()->json(['EventType' => $eventType]);
        }
        else {
            return response(['message' => trans('messages.eventTypeUpdateError')], 409);
        }
    }

    public function delete($id) {
        $eventType = EventType::getEventTypeByID($id);
        if($eventType == null) {
            return response(['message' => trans('messages.eventTypeDoesntExistError')], 404);
        }

        //TODO: Check if doesn't appear in any event

        $eventType->delete();

        return response('', 204);
    }
}
