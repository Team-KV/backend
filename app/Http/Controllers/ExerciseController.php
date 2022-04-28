<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Exercise;
use App\Models\ExerciseFile;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    /**
     * Returns response with exercises in JSON
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return $this->sendData(Exercise::getAllExercises());
    }

    /**
     * Returns response with created exercise in JSON
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create(Request $request): Response|JsonResponse
    {
        $params = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['string', 'nullable'],
            'url' => ['string', 'nullable'],
            'category_id' => ['numeric', 'nullable']
        ]);

        if(isset($params['category_id']) && $params['category_id'] != null && Category::getCategoryByID($params['category_id']) == null) {
            return $this->sendNotFound('messages.categoryDoesntExistError');
        }

        try {
            $exercise = Exercise::create($params);
        } catch (QueryException) {
            return $this->sendInternalError('messages.exerciseCreateError');
        }

        Storage::makeDirectory('exercises/'.$exercise->id);

        $files = $request->file('files');
        $uploaded = [];
        if(is_array($files)) {
            $uploaded = self::uploadFilesToExercise($exercise, $files);
        }

        return $this->sendData(['Exercise' => $exercise, 'ExerciseFiles' => $uploaded, 'Count' => count($uploaded)]);
    }

    /**
     * Returns response with exercise by ID in JSON
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $exercise = Exercise::getExerciseWithFilesByID($id);
        if ($exercise == null) {
            return $this->sendNotFound('messages.exerciseDoesntExistError');
        }

        return $this->sendData(['Exercise' => $exercise]);
    }

    /**
     * Returns response with updated exercise in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $exercise = Exercise::getExerciseByID($id);
        if ($exercise == null) {
            return $this->sendNotFound('messages.exerciseDoesntExistError');
        }

        $params = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['string', 'nullable'],
            'url' => ['string', 'nullable'],
            'category_id' => ['numeric', 'nullable']
        ]);

        if(isset($params['category_id']) && $params['category_id'] != null && Category::getCategoryByID($params['category_id']) == null) {
            return $this->sendNotFound('messages.categoryDoesntExistError');
        }

        if (Exercise::updateExercise($exercise, $params)) {
            return $this->sendData(['Exercise' => $exercise]);
        } else {
            return $this->sendInternalError('messages.exerciseUpdateError');
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
        $exercise = Exercise::getExerciseByID($id);
        if ($exercise == null) {
            return $this->sendNotFound('messages.exerciseDoesntExistError');
        }

        ExerciseFile::removeFilesByExerciseID($id);

        Storage::delete(Storage::files('exercises/'.$exercise->id));
        Storage::deleteDirectory('exercises/'.$exercise->id);

        $exercise->delete();

        return $this->sendNoContent();
    }

    /**
     * Returns response with uploaded files in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function upload($id, Request $request): Response|JsonResponse
    {
        $exercise = Exercise::getExerciseByID($id);
        if ($exercise == null) {
            return $this->sendNotFound('messages.exerciseDoesntExistError');
        }

        $files = $request->file('files');
        $uploaded = [];
        if(is_array($files)) {
            $uploaded = self::uploadFilesToExercise($exercise, $files);
        }

        return $this->sendData(['ExerciseFiles' => $uploaded, 'Count' => count($uploaded)]);
    }

    /**
     * Uploads files to exercise
     *
     * @param Exercise $exercise
     * @param array $files
     * @return array
     */
    private static function uploadFilesToExercise(Exercise $exercise, array $files): array
    {
        $allowedFileExtension = ['jpeg', 'jpg', 'png', 'mp4', 'avi', 'mov'];
        $uploaded = [];

        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            if (in_array($extension, $allowedFileExtension)) {
                if(Storage::exists('exercises/' . $exercise->id . '/' . $exercise->id . '_' . $filename)) {
                    continue;
                }
                $exerciseFile = ExerciseFile::create(['file_name' => $filename, 'type' => $extension, 'exercise_id' => $exercise->id, 'url' => 'exercise-file/' . $exercise->id . '/' . $filename]);
                Storage::put('exercises/' . $exercise->id . '/' . $exercise->id . '_' . $filename, file_get_contents($file));
                array_push($uploaded, $exerciseFile);
            }
        }

        return $uploaded;
    }
}
