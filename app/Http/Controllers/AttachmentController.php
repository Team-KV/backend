<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Returns file by ID
     *
     * @param $id
     * @param $filename
     * @return Response|StreamedResponse
     */
    public function download($id, $filename): Response|StreamedResponse
    {
        if(Storage::exists('clients/' . $id . '/' . $id . '_' . $filename)) {
            return Storage::download('clients/' . $id . '/' . $id . '_' . $filename);
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
        $attachment = Attachment::getFileByID($id);
        if($attachment == null) {
            return $this->sendNotFound('messages.fileDoesntExistError');
        }

        Storage::delete('clients/' . $attachment->client_id . '/' . $attachment->file_name);

        $attachment->delete();

        return $this->sendNoContent();
    }
}
