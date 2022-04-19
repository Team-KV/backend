<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Returns file by ID
     *
     * @param $id
     * @return Response|StreamedResponse
     */
    public function download($id): Response|StreamedResponse
    {
        $attachment = Attachment::getFileByID($id);
        if($attachment == null) {
            $this->sendNotFound('messages.fileDoesntExistError');
        }

        $path = 'clients/' . $attachment->client_id . '/' . $attachment->file_name;
        if(Storage::exists($path)) {
            return Storage::download($path);
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
