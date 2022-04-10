<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Event;
use App\Models\ExerciseTask;
use App\Models\Task;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Js;
use PHPUnit\Util\Json;

class TaskController extends Controller
{
    /**
     * Returns response with tasks by event ID in JSON
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function list(Request $request): Response|JsonResponse
    {
        if($request->has('event-id')) {
            $event = Event::getEventByID($request->query('event-id'));
            if($event == null) {
                return response(['message' => trans('messages.eventDoesntExistError')], 404);
            }
            return response()->json(Task::getAllTasksByEventID($request->query('event-id')));
        }
        else {
            return response(['message' => trans('messages.missingEventIdParameterError')], 409);
        }
    }

    /**
     * Returns response with created task in JSON
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create(Request $request): Response|JsonResponse
    {
        $params = $request->validate([
            'text' => ['string', 'nullable'],
            'is_active' => ['required', 'bool'],
            'client_id' => ['required', 'numeric'],
            'event_id' => ['required', 'numeric']
        ]);

        if(Client::getClientByID($params['client_id']) == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        if(Event::getEventByID($params['event_id']) == null) {
            return response(['message' => trans('messages.eventDoesntExistError')], 404);
        }

        try {
            $task = Task::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.taskCreateError')], 409);
        }

        return response()->json(['Task' => $task]);
    }

    /**
     * Returns response with task by ID
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $task = Task::getTaskWithExercisesByID($id);
        if($task == null) {
            return response(['message' => trans('messages.taskDoesntExistError')], 404);
        }

        return response()->json(['Task' => $task]);
    }

    /**
     * Returns response with updated task
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $task = Task::getTaskByID($id);
        if($task == null) {
            return response(['message' => trans('messages.taskDoesntExistError')], 404);
        }

        $params = $request->validate([
            'text' => ['string', 'nullable'],
            'is_active' => ['required', 'bool'],
            'client_id' => ['required', 'numeric'],
            'event_id' => ['required', 'numeric']
        ]);

        if(Client::getClientByID($params['client_id']) == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        if(Event::getEventByID($params['event_id']) == null) {
            return response(['message' => trans('messages.eventDoesntExistError')], 404);
        }

        if(Task::updateTask($task, $params)) {
            return response()->json(['Task' => $task]);
        }
        else {
            return response(['message' => trans('messages.taskUpdateError')], 409);
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
        $task = Task::getTaskByID($id);
        if($task == null) {
            return response(['message' => trans('messages.taskDoesntExistError')], 404);
        }

        ExerciseTask::deleteByTaskID($id);

        $task->delete();

        return response('', 204);
    }

    public function changeStatus($id): Response|JsonResponse
    {
        $task = Task::getTaskByID($id);
        if($task == null) {
            return response(['message' => trans('messages.taskDoesntExistError')], 404);
        }

        $task->is_active = !$task->is_active;

        $task->save();

        return response()->json(['Task' => $task]);
    }
}
