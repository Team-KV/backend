<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    /**
     * Returns response with tasks by event ID in JSON
     *
     * @param $event_id
     * @return JsonResponse
     */
    public function list($event_id): JsonResponse
    {
        return response()->json(Task::getAllTasksByEventID($event_id));
    }
}
