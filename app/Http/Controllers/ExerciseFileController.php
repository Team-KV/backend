<?php

namespace App\Http\Controllers;

use App\Models\ExerciseFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExerciseFileController extends Controller
{
    /**
     * Returns file by ID
     *
     * @param $id
     * @return Response|BinaryFileResponse
     */
    public function download($id): Response|BinaryFileResponse
    {
        $exerciseFile = ExerciseFile::getFileByID($id);
        if($exerciseFile == null) {
            return $this->sendNotFound('messages.fileDoesntExistError');
        }

        $path = 'app/exercises/' . $exerciseFile->exercise_id . '/' . $exerciseFile->file_name;
        $filepath = storage_path($path);
        return response()->file($filepath);
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
            return $this->sendNotFound('messages.fileDoesntExistError');
        }

        Storage::delete('exercises/' . $exerciseFile->exercise_id . '/' . $exerciseFile->file_name);

        $exerciseFile->delete();

        return $this->sendNoContent();
    }
}
