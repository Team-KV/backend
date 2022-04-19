<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Task;
use DateTime;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * Returns response with collection of events in JSON
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return $this->sendData(Event::getAllEvents());
    }

    /**
     * Returns response with collection of events by filter in JSON
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        if($request->has('period')) {
            if ($request->has('datetime')) {
                return $this->sendData(Event::getAllEventsByFilter($request->query('datetime'), $request->query('period')));
            } else {
                return $this->sendData(Event::getAllEventsByFilter(NOW()->toString(), $request->query('period')));
            }
        } else {
            if ($request->has('datetime')) {
                return $this->sendData(Event::getAllEventsByFilter($request->query('datetime')));
            } else {
                return $this->sendData(Event::getAllEventsByFilter(NOW()->toString()));
            }
        }
    }

    /**
     * Returns response with created event
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create(Request $request): Response|JsonResponse
    {
        $params = $request->validate([
            'name' => ['required', 'string'],
            'start' => ['required', 'date_format:Y-m-d H:i:s'],
            'end' => ['required', 'date_format:Y-m-d H:i:s'],
            'note' => ['string', 'nullable'],
            'client_id' => ['required', 'numeric'],
            'staff_id' => ['required', 'numeric'],
            'event_type_id' => ['required', 'numeric']
        ]);

        try {
            $params['start'] = new DateTime($params['start']);
            $params['end'] = new DateTime($params['end']);
        } catch(Exception) {
            return $this->sendConflict('messages.eventDateFormatError');
        }

        if(!Event::checkFreeTime($params['staff_id'], $params['start'], $params['end'])) {
            return $this->sendConflict('messages.eventDateTimeError');
        }

        try {
            $event = Event::create($params);
        } catch (QueryException) {
            return $this->sendInternalError('messages.eventCreateError');
        }

        return $this->sendData(['Event' => $event]);
    }

    /**
     * Returns response with event by ID
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $event = Event::getEventWithAllByID($id);
        if($event == null) {
            return $this->sendNotFound('messages.eventDoesntExistError');
        }

        return $this->sendData(['Event' => $event]);
    }

    /**
     * Returns response with updated event in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $event = Event::getEventByID($id);
        if($event == null) {
            return $this->sendNotFound('messages.eventDoesntExistError');
        }

        $params = $request->validate([
            'name' => ['required', 'string'],
            'start' => ['required', 'date_format:Y-m-d H:i:s'],
            'end' => ['required', 'date_format:Y-m-d H:i:s'],
            'note' => ['string', 'nullable'],
            'client_id' => ['required', 'numeric'],
            'staff_id' => ['required', 'numeric'],
            'event_type_id' => ['required', 'numeric']
        ]);

        try {
            $params['start'] = new DateTime($params['start']);
            $params['end'] = new DateTime($params['end']);
        } catch(Exception) {
            return $this->sendConflict('messages.eventDateFormatError');
        }

        if(!Event::checkFreeTime($params['staff_id'], $params['start'], $params['end'], $event->id)) {
            return $this->sendConflict('messages.eventDateTimeError');
        }

        if(Event::updateEvent($event, $params)) {
            return $this->sendData(['Event' => $event]);
        }
        else {
            return $this->sendInternalError('messages.eventUpdateError');
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
        $event = Event::getEventByID($id);
        if($event == null) {
            return $this->sendNotFound('messages.eventDoesntExistError');
        }

        Event::deleteEvent($event);

        return $this->sendNoContent();
    }
}
