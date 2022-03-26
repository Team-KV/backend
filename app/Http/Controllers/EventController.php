<?php

namespace App\Http\Controllers;

use App\Models\Event;
use DateTime;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Returns response with collection of events
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        if($request->has('period')) {
            if ($request->has('datetime')) {
                return response()->json(Event::getAllEvents($request->query('datetime'), $request->query('period')));
            } else {
                return response()->json(Event::getAllEvents(NOW()->toString(), $request->query('period')));
            }
        } else {
            if ($request->has('datetime')) {
                return response()->json(Event::getAllEvents($request->query('datetime')));
            } else {
                return response()->json(Event::getAllEvents(NOW()->toString()));
            }
        }
    }

    public function create(Request $request)
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
            return response(['message' => trans('messages.eventDateFormatError')], 409);
        }

        if(!Event::checkFreeTime($params['staff_id'], $params['start'], $params['end'])) {
            return response(['message' => trans('messages.eventDateTimeError')], 409);
        }

        try {
            $event = Event::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.eventCreateError')], 409);
        }

        return response()->json(['Event' => $event]);
    }
}
