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
        return $this->sendData(EventType::getListOfTypes());
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
            return $this->sendConflict('messages.eventTypeAlreadyExistsError');
        }

        try {
            $eventType = EventType::create($params);
        } catch (QueryException) {
            return $this->sendInternalError('messages.eventTypeCreateError');
        }

        return $this->sendData(['EventType' => $eventType]);
    }

    /**
     * Returns response with event type object
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $eventType = EventType::getEventTypeByID($id);
        if($eventType == null) {
            return $this->sendNotFound('messages.eventTypeDoesntExistError');
        }

        return $this->sendData(['EventType' => $eventType]);
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
            return $this->sendNotFound('messages.eventTypeDoesntExistError');
        }

        $params = $request->validate([
            'name' => ['required', 'string']
        ]);

        if(EventType::getEventTypeByName($params['name']) != null) {
            return $this->sendConflict('messages.eventTypeAlreadyExistsError');
        }

        if(EventType::updateEventType($eventType, $params)) {
            return $this->sendData(['EventType' => $eventType]);
        }
        else {
            return $this->sendInternalError('messages.eventTypeUpdateError');
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
        $eventType = EventType::getEventTypeByID($id);
        if($eventType == null) {
            return $this->sendNotFound('messages.eventTypeDoesntExistError');
        }

        $events = $eventType->event;

        if(count($events) > 0) {
            return $this->sendConflict('messages.eventTypeDeleteError');
        }

        $eventType->delete();

        return $this->sendNoContent();
    }
}
