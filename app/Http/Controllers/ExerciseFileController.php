<?php

namespace App\Http\Controllers;

use App\Models\ExerciseFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ExerciseFileController extends Controller
{
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
