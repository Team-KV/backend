<?php

namespace App\Http\Controllers;

use App\Models\ExerciseFile;
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
    public function download($id, $filename): Response|StreamedResponse
    {
        if(Storage::exists('exercises/' . $id . '/' . $id . '_' . $filename)) {
            return Storage::download('exercises/' . $id . '/' . $id . '_' . $filename);
        }

        return $this->sendNotFound('messages.fileDoesntExistError');
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
