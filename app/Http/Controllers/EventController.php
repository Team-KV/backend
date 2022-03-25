<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
}
