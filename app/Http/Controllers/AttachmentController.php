<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Returns file by ID
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function download($id): Response|BinaryFileResponse
    {
        $attachment = Attachment::getFileByID($id);
        if($attachment == null) {
            $this->sendNotFound('messages.fileDoesntExistError');
        }

        $path = 'app/clients/' . $attachment->client_id . '/' . $attachment->file_name;
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
        $attachment = Attachment::getFileByID($id);
        if($attachment == null) {
            return $this->sendNotFound('messages.fileDoesntExistError');
        }

        Storage::delete('clients/' . $attachment->client_id . '/' . $attachment->file_name);

        $attachment->delete();

        return $this->sendNoContent();
    }
}
