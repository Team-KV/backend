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
            return $this->sendNotFound('messages.exerciseTaskDoesntExistError');
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
            return $this->sendNotFound('messages.exerciseDoesntExistError');
        }

        $task = Task::getTaskByID($params['task_id']);
        if($task == null) {
            return $this->sendNotFound('messages.taskDoesntExistError');
        }

        $task->exercises()->wherePivot('task_id', '=', $id)->first()->update($params);

        return $this->sendData(['ExerciseTask' => $exerciseTask]);
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
            return $this->sendNotFound('messages.exerciseTaskDoesntExistError');
        }

        $task = Task::getTaskByID($exerciseTask->task_id);
        if($task == null) {
            return $this->sendNotFound('messages.taskDoesntExistError');
        }

        $task->exercises()->detach($exerciseTask->exercise_id);

        return $this->sendNoContent();
    }
}
