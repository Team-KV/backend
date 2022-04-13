<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Client;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

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
            'sex' => ['numeric', 'nullable'],
            'height' => ['numeric', 'nullable'],
            'weight' => ['numeric', 'nullable'],
            'personal_information_number' => ['string', 'nullable'],
            'insurance_company' => ['numeric', 'nullable'],
            'phone' => ['string', 'max:16', 'nullable'],
            'contact_email' => ['string', 'email', 'nullable'],
            'street' => ['string', 'nullable'],
            'city' => ['string', 'nullable'],
            'postal_code' => ['string', 'max:6', 'nullable'],
            'sport' => ['string', 'nullable'],
            'past_illnesses' => ['string', 'nullable'],
            'injuries_suffered' => ['string', 'nullable'],
            'anamnesis' => ['string', 'nullable'],
            'note' => ['string', 'nullable'],
            'no_czech' => ['boolean'],
            'client_id' => ['integer', 'nullable']
        ]);

        if (Client::getClientByPIN($params['personal_information_number']) != null || User::getUserByEmail($params['contact_email']) != null) {
            return response(['message' => trans('messages.clientAlreadyExistsError')], 409);
        }

        if ($params['no_czech'] != true && $params['personal_information_number'] != null && $params['personal_information_number'] != '') {
            if (!Client::verifyPIN($params['personal_information_number'])) {
                return response(['message' => trans('messages.clientPINError')], 409);
            }
        }

        try {
            $client = Client::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.clientCreateError')], 409);
        }

        Storage::makeDirectory('clients/' . $client->id);

        if (isset($params['contact_email']) && $params['contact_email'] != "" && $params['contact_email'] != null) {
            if (!User::createUser($params['contact_email'], $client->id)) {
                return response('', 409)->json(['message' => trans('messages.userCreateError'), 'Client' => $client]);
            }
        }

        return response()->json(['Client' => $client]);
    }

    /**
     * Returns client by ID
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $client = Client::getClientWithAllByID($id);
        if ($client == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }
        return response()->json(['Client' => $client]);
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
        if (Client::getClientByID($id) == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        $params = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'date_born' => ['required', 'date'],
            'sex' => ['numeric', 'nullable'],
            'height' => ['numeric', 'nullable'],
            'weight' => ['numeric', 'nullable'],
            'personal_information_number' => ['string', 'nullable'],
            'insurance_company' => ['numeric', 'nullable'],
            'phone' => ['string', 'max:16', 'nullable'],
            'contact_email' => ['string', 'email', 'nullable'],
            'street' => ['string', 'nullable'],
            'city' => ['string', 'nullable'],
            'postal_code' => ['string', 'max:6', 'nullable'],
            'sport' => ['string', 'nullable'],
            'past_illnesses' => ['string', 'nullable'],
            'injuries_suffered' => ['string', 'nullable'],
            'anamnesis' => ['string', 'nullable'],
            'note' => ['string', 'nullable'],
            'no_czech' => ['boolean'],
            'client_id' => ['integer', 'nullable']
        ]);

        if ($params['no_czech'] != true && $params['personal_information_number'] != null && $params['personal_information_number'] != '') {
            if (!Client::verifyPIN($params['personal_information_number'])) {
                return response(['message' => trans('messages.clientPINError')], 409);
            }
        }

        if (Client::updateClientByID($id, $params)) {
            return response()->json(['Client' => Client::getClientWithAllByID($id)]);
        } else {
            return response(['message' => trans('messages.clientUpdateError')], 409);
        }
    }

    /**
     * Returns response after success delete
     *
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $client = Client::getClientByID($id);
        if ($client == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        Attachment::removeFilesByClientID($id);

        Storage::delete(Storage::files('clients/' . $client->id));
        Storage::deleteDirectory('clients/' . $client->id);
        //TODO: Delete client's objects (events, attachments, ...)

        Client::deleteClientByID($id);

        return response('', 204);
    }

    /**
     * Returns response with created user in JSON
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function createUser($id): Response|JsonResponse
    {
        $client = Client::getClientByID($id);
        if ($client != null) {
            if ($client->contact_email != '' && $client->contact_email != null) {
                if (!User::createUser($client->contact_email, $client->id)) {
                    return response('', 409)->json(['message' => trans('messages.userCreateError'), 'Client' => $client]);
                }
            } else {
                return response(['message' => trans('messages.clientEmailError')], 409);
            }
        } else {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        return response()->json(['Client' => Client::getClientWithAllByID($id)]);
    }

    /**
     * Returns response with graph data for specific client in JSON
     *
     * @param $id
     * @return JsonResponse
     */
    public function graph($id): JsonResponse
    {
        return response()->json(['GraphData' => Client::getGraphData($id)]);
    }

    /**
     * Returns response with uploaded attachments in JSON
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function uploadAttachments($id, Request $request): Response|JsonResponse
    {
        $client = Client::getClientByID($id);
        if ($client == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        $files = $request->file('files');
        $uploaded = [];
        if(is_array($files)) {
            $uploaded = self::uploadAttachmentsToClient($client, $files);
        }

        return response()->json(['Attachments' => $uploaded, 'Count' => count($uploaded)]);
    }

    /**
     * Returns response with client in JSON after attach tags
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function attachTags($id, Request $request): Response|JsonResponse
    {
        $client = Client::getClientWithAllByID($id);
        if ($client == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        $params = $request->validate([
            'tag_ids' => ['required', 'array']
        ]);

        foreach($params['tag_ids'] as $tag_id) {
            if(Tag::getTagByID($tag_id) != null) {
                $client->tags()->attach($tag_id);
            }
        }

        return response()->json(['Client' => $client]);
    }

    /**
     * Returns response with client in JSON after detach tag
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function detachTag($id, Request $request): Response|JsonResponse
    {
        $client = Client::getClientWithAllByID($id);
        if ($client == null) {
            return response(['message' => trans('messages.clientDoesntExistsError')], 404);
        }

        $params = $request->validate([
            'tag_id' => ['required', 'numeric']
        ]);

        if(Tag::getTagByID($params['tag_id']) != null) {
            $client->tags()->attach($params['tag_id']);
        }
        else {
            return response(['message' => trans('messages.tagDoesntExistError')], 404);
        }

        return response()->json(['Client' => $client]);
    }

    /**
     * Uploads attachments to client
     *
     * @param Client $client
     * @param array $files
     * @return array
     */
    private static function uploadAttachmentsToClient(Client $client, array $files): array
    {
        $allowedFileExtension = ['jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx'];
        $uploaded = [];

        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            if (in_array($extension, $allowedFileExtension)) {
                $attachment = Attachment::create(['file_name' => $filename, 'type' => $extension, 'client_id' => $client->id]);
                Storage::put('clients/' . $client->id . '/' . $filename, file_get_contents($file));
                array_push($uploaded, $attachment);
            }
        }

        return $uploaded;
    }
}
