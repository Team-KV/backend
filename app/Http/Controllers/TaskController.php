<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Event;
use App\Models\Exercise;
use App\Models\ExerciseTask;
use App\Models\Task;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
                return $this->sendNotFound('messages.eventDoesntExistError');
            }

            return $this->sendData(Task::getAllTasksByEventID($request->query('event-id')));
        }
        else {
            return $this->sendBadRequest('messages.missingEventIdParameterError');
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
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        if(Event::getEventByID($params['event_id']) == null) {
            return $this->sendNotFound('messages.eventDoesntExistError');
        }

        try {
            $task = Task::create($params);
        } catch (QueryException) {
            return $this->sendInternalError('messages.taskCreateError');
        }

        return $this->sendData(['Task' => $task]);
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
            return $this->sendNotFound('messages.taskDoesntExistError');
        }

        return $this->sendData(['Task' => $task]);
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
            return $this->sendNotFound('messages.taskDoesntExistError');
        }

        $params = $request->validate([
            'text' => ['string', 'nullable'],
            'is_active' => ['required', 'bool'],
            'client_id' => ['required', 'numeric'],
            'event_id' => ['required', 'numeric']
        ]);

        if(Client::getClientByID($params['client_id']) == null) {
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        if(Event::getEventByID($params['event_id']) == null) {
            return $this->sendNotFound('messages.eventDoesntExistError');
        }

        if(Task::updateTask($task, $params)) {
            return $this->sendData(['Task' => $task]);
        }
        else {
            return $this->sendInternalError('messages.taskUpdateError');
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
            return $this->sendNotFound('messages.taskDoesntExistError');
        }

        Task::removeExercisesFromTask($id);

        $task->delete();

        return $this->sendNoContent();
    }

    /**
     * Returns response with task with changed status in JSON
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function changeStatus($id): Response|JsonResponse
    {
        $task = Task::getTaskByID($id);
        if($task == null) {
            return $this->sendNotFound('messages.taskDoesntExistError');
        }

        $task->is_active = !$task->is_active;

        $task->save();

        return $this->sendData(['Task' => $task]);
    }

    /**
     * Returns response with added exercise tasks in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function addExercises($id, Request $request): Response|JsonResponse
    {
        $task = Task::getTaskByID($id);
        if($task == null) {
            return $this->sendNotFound('messages.taskDoesntExistError');
        }

        $params = $request->validate([
            'exerciseTasks' => ['required', 'array']
        ]);

        foreach($params['exerciseTasks'] as $exerciseTask) {
            if(Exercise::getExerciseByID($exerciseTask['exercise_id']) != null) {
                try {
                    $task->exercises()->attach($exerciseTask['exercise_id'], [
                        'task_id' => $id,
                        'feedback' => $exerciseTask['feedback'],
                        'difficulty' => $exerciseTask['difficulty'],
                        'repetitions' => $exerciseTask['repetitions'],
                        'duration' => $exerciseTask['duration']
                    ]);
                } catch (QueryException) {}
            }
        }

        return $this->sendData(['ExerciseTasks' => ExerciseTask::getExerciseTasksByTaskID($id)]);
    }
}
