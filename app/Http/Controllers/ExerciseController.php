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
        return response()->json(Exercise::getAllExercises());
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
            return response(['message' => trans('messages.categoryDoesntExistError')], 404);
        }

        try {
            $exercise = Exercise::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.exerciseCreateError')], 409);
        }

        Storage::makeDirectory('exercises/'.$exercise->id);

        $allowedFileExtension=['jpeg','jpg','png','mp4','avi','mov'];
        $files = $request->file('files');
        $counter = 0;
        $uploaded = [];
        foreach($files as $file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            if(in_array($extension, $allowedFileExtension)) {
                $exerciseFile = ExerciseFile::create(['file_name' => $filename, 'type' => $extension, 'exercise_id' => $exercise->id]);
                Storage::put('exercises/'.$exercise->id.'/'.$filename, file_get_contents($file));
                array_push($uploaded, $exerciseFile);
                $counter++;
            }
        }

        return response()->json(['Exercise' => $exercise, 'ExerciseFiles' => $uploaded, 'Count' => $counter]);
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
            return response(['message' => trans('messages.exerciseDoesntExistError')], 404);
        }

        return response()->json(['Exercise' => $exercise]);
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
            return response(['message' => trans('messages.exerciseDoesntExistError')], 404);
        }

        $params = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['string', 'nullable'],
            'url' => ['string', 'nullable'],
            'category_id' => ['numeric', 'nullable']
        ]);

        if(isset($params['category_id']) && $params['category_id'] != null && Category::getCategoryByID($params['category_id']) == null) {
            return response(['message' => trans('messages.categoryDoesntExistError')], 404);
        }

        if (Exercise::updateExercise($exercise, $params)) {
            return response()->json(['Exercise' => $exercise]);
        } else {
            return response(['message' => trans('messages.exerciseUpdateError')], 409);
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
            return response(['message' => trans('messages.exerciseDoesntExistError')], 404);
        }

        ExerciseFile::removeFilesByExerciseID($id);

        Storage::delete(Storage::files('exercises/'.$exercise->id));
        Storage::deleteDirectory('exercises/'.$exercise->id);

        $exercise->delete();

        return response('', 204);
    }
}
