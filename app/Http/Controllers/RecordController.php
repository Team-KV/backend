<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Record;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    /**
     * Returns response with created record object
     *
     * @param $event_id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create($event_id, Request $request): Response|JsonResponse
    {
        $event = Event::getEventWithAllByID($event_id);
        if($event == null) {
            return $this->sendNotFound('messages.eventDoesntExistError');
        }

        if($event->record != null) {
            return $this->sendConflict('messages.recordAlreadyExistsError');
        }

        $params = $request->validate([
            'progress' => ['required', 'numeric'],
            'progress_note' => ['string', 'nullable'],
            'exercise_note' => ['string', 'nullable'],
            'text' => ['string', 'nullable']
        ]);
        $params['event_id'] = $event_id;

        try {
            $record = Record::create($params);
            Log::channel('record')->info('Create record.', ['author_id' => Auth::user()->id, 'Record' => $record]);
        } catch (QueryException) {
            return $this->sendInternalError('messages.recordCreateError');
        }

        return $this->sendData(['Record' => $record]);
    }

    /**
     * Returns record by ID in JSON response
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function detail($id): Response|JsonResponse
    {
        $record = Record::getRecordByID($id);
        if($record == null) {
            return $this->sendNotFound('messages.recordDoesntExistError');
        }

        return $this->sendData(['Record' => $record]);
    }

    /**
     * Returns response with updated record object
     *
     * @param $id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function update($id, Request $request): Response|JsonResponse
    {
        $record = Record::getRecordByID($id);
        if($record == null) {
            return $this->sendNotFound('messages.recordDoesntExistError');
        }

        $params = $request->validate([
            'progress' => ['required', 'numeric'],
            'progress_note' => ['string', 'nullable'],
            'exercise_note' => ['string', 'nullable'],
            'text' => ['string', 'nullable']
        ]);

        if(Record::updateRecord($record, $params)) {
            Log::channel('record')->info('Update record', ['author_id' => Auth::user()->id, 'record_id' => $record->id, 'Params' => $params]);
            return $this->sendData(['Record' => $record]);
        }
        else {
            return $this->sendInternalError('messages.recordUpdateError');
        }
    }

    /**
     * Deletes record
     *
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $record = Record::getRecordByID($id);
        if($record == null) {
            return $this->sendNotFound('messages.recordDoesntExistError');
        }

        Log::channel('record')->info('Delete record', ['author_id' => Auth::user()->id, 'record_id' => $record->id]);
        $record->delete();

        return $this->sendNoContent();
    }
}
