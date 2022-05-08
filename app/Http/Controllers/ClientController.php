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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            Log::channel('client')->info('Client create.', ['author_id' => Auth::user()->id, 'Client' => $client]);
        } catch (QueryException) {
            return $this->sendInternalError('messages.clientCreateError');
        }

        Storage::makeDirectory('clients/' . $client->id);

        if(isset($params['contact_email']) && $params['contact_email'] != "" && $params['contact_email'] != null) {
            if(!User::createUser($params['contact_email'], $client->id)) {
                return $this->sendResponse('', 500, ['message' => trans('messages.userCreateError'), 'Client' => $client]);
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
            Log::channel('client')->info('Update client.', ['author_id' => Auth::user()->id, 'client_id' => $id, 'Params' => $params]);
            return $this->sendData(['Client' => Client::getClientWithAllByID($id)]);
        } else {
            return $this->sendInternalError('messages.clientUpdateError');
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

        Log::channel('client')->info('Delete client.', ['author_id' => Auth::user()->id, 'client_id' => $id]);
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
                    return $this->sendResponse('', 500, ['message' => trans('messages.userCreateError'), 'Client' => $client]);
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
     * @param $tag_id
     * @return Response|JsonResponse
     */
    public function detachTag($id, $tag_id): Response|JsonResponse
    {
        $client = Client::getClientByID($id);
        if ($client == null) {
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        if(Tag::getTagByID($tag_id) != null) {
            $client->tags()->detach($tag_id);
        }
        else {
            return $this->sendNotFound('messages.tagDoesntExistError');
        }

        return $this->sendData(['Client' => Client::getClientWithAllByID($id)]);
    }

    /**
     * Returns response with clients by tag in JSON
     *
     * @param $tag
     * @return JsonResponse
     */
    public function searchByTag($tag): JsonResponse
    {
        $tag = Tag::getTagByName($tag);
        if($tag == null) {
            return $this->sendData([]);
        }

        return $this->sendData($tag->clients);
    }

    /**
     * Returns response with url to file with exported data in JSON
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function exportData($id): Response|JsonResponse
    {
        $client = Client::getClientByID($id);
        if ($client == null) {
            return $this->sendNotFound('messages.clientDoesntExistsError');
        }

        if(Storage::exists('clients/' . $client->id . '/Export_client_' . $client->id . '_personal_info.csv')) {
            Storage::delete('clients/' . $client->id . '/Export_client_' . $client->id . '_personal_info.csv');
        }
        $export = "First name,Last name,Date born,Sex,Personal information number,Insurance company,Height,Weight,Phone,Contact email,Street,city,Postal code,Sport,Past illnesses,Injuries suffered,Anamnesis
$client->first_name,$client->last_name,$client->date_born,$client->sex,$client->personal_information_number,$client->insurance_company,$client->height,$client->weight,$client->phone,$client->contact_email,$client->street,$client->city,$client->postal_code,$client->sport,$client->past_illnesses,$client->injuries_suffered,$client->anamnesis";
        Storage::put('clients/' . $client->id . '/Export_client_' . $client->id . '_personal_info.csv', $export);

        return $this->sendData(['url' => 'export/' . $client->id . '/Export_client_' . $client->id . '_personal_info.csv']);
    }

    /**
     * Returns file by client_id and filename
     *
     * @param $id
     * @param $filename
     * @return Response|StreamedResponse
     */
    public function downloadExport($id, $filename): Response|StreamedResponse
    {
        if(Storage::exists('clients/' . $id . '/' . $filename)) {
            return Storage::download('clients/' . $id . '/' . $filename);
        }

        return $this->sendNotFound('messages.fileDoesntExistError');
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
                if(Storage::exists('clients/' . $client->id . '/' . $client->id . '_' . $filename)) {
                    continue;
                }
                $attachment = Attachment::create(['file_name' => $filename, 'type' => $extension, 'client_id' => $client->id, 'url' => 'attachment/' . $client->id . '/' . $filename]);
                Storage::put('clients/' . $client->id . '/' . $client->id . '_' . $filename, file_get_contents($file));
                array_push($uploaded, $attachment);
            }
        }

        return $uploaded;
    }
}
