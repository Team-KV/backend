<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Returns collection of clients in JSON
     *
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        return response()->json(Client::getListOfClients());
    }
}
