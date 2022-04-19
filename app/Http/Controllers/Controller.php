<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Returns JSON response with data
     *
     * @param $data
     * @return JsonResponse
     */
    public function sendData($data): JsonResponse
    {
        return response()->json($data);
    }

    /**
     * Returns response with no content
     *
     * @return Response
     */
    public function sendNoContent(): Response
    {
        return response('', 204);
    }

    /**
     * Returns response with not found message
     *
     * @param string $message
     * @return Response
     */
    public function sendNotFound(string $message): Response
    {
        return response(['message' => trans($message)], 404);
    }

    /**
     * Returns response with conflict message
     *
     * @param string $message
     * @return Response
     */
    public function sendConflict(string $message): Response
    {
        return response(['message' => trans($message)], 409);
    }

    /**
     * Returns response with internal server error message
     *
     * @param string $message
     * @return Response
     */
    public function sendInternalError(string $message): Response
    {
        return response(['message' => trans($message)], 500);
    }

    /**
     * Returns custom response with content, code and data
     *
     * @param string $content
     * @param int $code
     * @param $data
     * @return JsonResponse
     */
    public function sendResponse(string $content, int $code, $data): JsonResponse
    {
        return response($content, $code)->json($data);
    }
}
