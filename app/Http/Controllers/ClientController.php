<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Integer;

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

    /**
     * Creates new client object
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create(Request $request): Response|JsonResponse
    {
        $params = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'date_born' => ['required', 'date'],
            'sex' => ['numeric'],
            'height' => ['numeric'],
            'weight' => ['numeric'],
            'personal_information_number' => [],
            'insurance_company' => ['numeric'],
            'phone' => ['required', 'max:16'],
            'contact_email' => ['email'],
            'street' => [],
            'city' => [],
            'postal_code' => ['max:6'],
            'sport' => [],
            'past_illnesses' => [],
            'injuries_suffered' => [],
            'diag' => [],
            'note' => []
        ]);

        if(Client::getClientByPIN($params['personal_information_number']) != null || User::getUserByEmail($params['contact_email']) != null) {
            return response(['message' => trans('messages.clientAlreadyExistsError')], 409);
        }

        //TODO: Personal information number verification

        try {
            $client = Client::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.clientCreateError')], 409);
        }

        if(isset($params['contact_email']) && $params['contact_email'] != "") {
            $password = Str::random(8);
            $userParams = ['email' => $params['contact_email'],
                'password' => Hash::make($password),
                'role' => 0,
                'staff_id' => null,
                'client_id' => $client->id];
            try {
                User::create($userParams);
            } catch(QueryException) {
                return response('', 409)->json(['message' => trans('messages.userCreateError'), 'Client' => $client]);
            }

            //TODO: Mail with password
        }

        return response()->json(['Client' => $client]);
    }

    /**
     * Returns client by ID
     *
     * @param $id
     * @return JsonResponse
     */
    public function detail($id): JsonResponse
    {
        return response()->json(['Client' => Client::getClientByID($id)]);
    }

    /**
     * Updates client by ID
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function update($id, Request $request): JsonResponse|Response
    {
        if(Client::getClientByID($id) == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        $params = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'date_born' => ['required', 'date'],
            'sex' => ['numeric'],
            'height' => ['numeric'],
            'weight' => ['numeric'],
            'personal_information_number' => [],
            'insurance_company' => ['numeric'],
            'phone' => ['required', 'max:16'],
            'contact_email' => ['email'],
            'street' => [],
            'city' => [],
            'postal_code' => ['max:6'],
            'sport' => [],
            'past_illnesses' => [],
            'injuries_suffered' => [],
            'diag' => [],
            'note' => []
        ]);

        //TODO: Personal information number verification

        if(Client::updateClientByID($id, $params)) {
            return response()->json(['Client' => Client::getClientByID($id)]);
        }
        else {
            return response(['message' => trans('messages.clientUpdateError')], 409);
        }
    }

    public function delete($id): Response
    {
        if(Client::getClientByID($id) == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }
        //TODO: Delete client's objects (events, attachments, ...)

        Client::deleteClientByID($id);

        return response('', 204);
    }
}
