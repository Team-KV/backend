<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Client;
use App\Models\Event;
use App\Models\Tag;
use App\Models\User;
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
        return $this->sendData(Client::getListOfClients());
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
            return $this->sendConflict('messages.clientAlreadyExistsError');
        }

        if ($params['no_czech'] != true && $params['personal_information_number'] != null && $params['personal_information_number'] != '') {
            if (!Client::verifyPIN($params['personal_information_number'])) {
                return $this->sendConflict('messages.clientPINError');
            }
        }

        try {
            $client = Client::create($params);
        } catch (QueryException) {
            return $this->sendConflict('messages.clientCreateError');
        }

        Storage::makeDirectory('clients/' . $client->id);

        if (isset($params['contact_email']) && $params['contact_email'] != "" && $params['contact_email'] != null) {
            if (!User::createUser($params['contact_email'], $client->id)) {
                return $this->sendResponse('', 409, ['message' => trans('messages.userCreateError'), 'Client' => $client]);
            }
        }

        return $this->sendData(['Client' => $client]);
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
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        return $this->sendData(['Client' => $client]);
    }

    /**
     * Updates client by ID
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        if (Client::getClientByID($id) == null) {
            return $this->sendNotFound('messages.clientDoesntExistsError');
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
                return $this->sendConflict('messages.clientPINError');
            }
        }

        if (Client::updateClientByID($id, $params)) {
            return $this->sendData(['Client' => Client::getClientWithAllByID($id)]);
        } else {
            return $this->sendConflict('messages.clientUpdateError');
        }
    }

    /**
     * Returns response after success delete
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function delete($id): Response|JsonResponse
    {
        $client = Client::getClientByID($id);
        if ($client == null) {
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        if(count($client->children) != 0) {
            return $this->sendConflict('messages.clientParentError');
        }

        Attachment::removeFilesByClientID($id);

        Storage::delete(Storage::files('clients/' . $client->id));
        Storage::deleteDirectory('clients/' . $client->id);

        $client->tags()->detach();

        foreach($client->events as $event) {
            Event::deleteEvent($event);
        }

        if($client->user != null) {
            $client->user->delete();
        }

        $client->delete();

        return $this->sendNoContent();
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
                    return $this->sendResponse('', 409, ['message' => trans('messages.userCreateError'), 'Client' => $client]);
                }
            } else {
                return $this->sendConflict('messages.clientEmailError');
            }
        } else {
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        return $this->sendData(['Client' => Client::getClientWithAllByID($id)]);
    }

    /**
     * Returns response with graph data for specific client in JSON
     *
     * @param $id
     * @return JsonResponse
     */
    public function graph($id): JsonResponse
    {
        return $this->sendData(['GraphData' => Client::getGraphData($id)]);
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
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        $files = $request->file('files');
        $uploaded = [];
        if(is_array($files)) {
            $uploaded = self::uploadAttachmentsToClient($client, $files);
        }

        return $this->sendData(['Attachments' => $uploaded, 'Count' => count($uploaded)]);
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
        $client = Client::getClientByID($id);
        if ($client == null) {
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        $params = $request->validate([
            'tag_ids' => ['required', 'array']
        ]);

        foreach($params['tag_ids'] as $tag_id) {
            if(Tag::getTagByID($tag_id) != null) {
                $client->tags()->attach($tag_id);
            }
        }

        return $this->sendData(['Client' => Client::getClientWithAllByID($id)]);
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
        $client = Client::getClientByID($id);
        if ($client == null) {
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        $params = $request->validate([
            'tag_id' => ['required', 'numeric']
        ]);

        if(Tag::getTagByID($params['tag_id']) != null) {
            $client->tags()->detach($params['tag_id']);
        }
        else {
            return $this->sendNotFound('messages.tagDoesntExistError');
        }

        return $this->sendData(['Client' => Client::getClientWithAllByID($id)]);
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
