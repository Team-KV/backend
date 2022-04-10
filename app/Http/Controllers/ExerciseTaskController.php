<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\ExerciseTask;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExerciseTaskController extends Controller
{
    /**
     * Returns response with updated exercise task in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $exerciseTask = ExerciseTask::getExerciseTaskByID($id);
        if($exerciseTask == null) {
            return response(['message' => trans('messages.exerciseTaskDoesntExistError')], 404);
        }

        $params = $request->validate([
            'feedback' => ['string', 'nullable'],
            'difficulty' => ['numeric', 'nullable'],
            'repetitions' => ['numeric', 'nullable'],
            'duration' => ['numeric', 'nullable'],
            'exercise_id' => ['required', 'numeric'],
            'task_id' => ['required', 'numeric']
        ]);

        if(Exercise::getExerciseByID($params['exercise_id']) == null) {
            return response(['message' => trans('messages.exerciseDoesntExistError')], 404);
        }

        $task = Task::getTaskByID($params['task_id']);
        if($task == null) {
            return response(['message' => trans('messages.taskDoesntExistError')], 404);
        }

        $task->exercises()->wherePivot('task_id', '=', $id)->first()->update($params);

        return response()->json(['ExerciseTask' => $exerciseTask]);
    }

    /**
     * Returns response after success delete
     *
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $exerciseTask = ExerciseTask::getExerciseTaskByID($id);
        if($exerciseTask == null) {
            return response(['message' => trans('messages.exerciseTaskDoesntExistError')], 404);
        }

        $task = Task::getTaskByID($exerciseTask->task_id);
        if($task == null) {
            return response(['message' => trans('messages.taskDoesntExistError')], 404);
        }

        $task->exercises()->detach($exerciseTask->exercise_id);

        return response('', 204);
    }
}
