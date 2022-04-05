<?php

namespace App\Http\Controllers;

use App\Models\ExerciseFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExerciseFileController extends Controller
{
    /**
     * Returns file by ID
     *
     * @param $id
     * @return Response|StreamedResponse
     */
    public function download($id): Response|StreamedResponse
    {
        $exerciseFile = ExerciseFile::getFileByID($id);
        if($exerciseFile == null) {
            return response(['message' => trans('messages.fileDoesntExistError')], 404);
        }

        $path = 'exercises/' . $exerciseFile->exercise_id . '/' . $exerciseFile->file_name;
        if(Storage::exists($path)) {
            return Storage::download($path);
        }

        return response(['message' => trans('messages.fileDoesntExistError')], 404);
    }

    /**
     * Returns response after success delete
     *
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $exerciseFile = ExerciseFile::getFileByID($id);
        if($exerciseFile == null) {
            return response(['message' => trans('messages.fileDoesntExistError')], 404);
        }

        Storage::delete('exercises/' . $exerciseFile->exercise_id . '/' . $exerciseFile->file_name);

        $exerciseFile->delete();

        return response('', 204);
    }
}
