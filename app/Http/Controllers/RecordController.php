<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecordController extends Controller
{
    /**
     * Returns collection of records for specific event in JSON response
     *
     * @param $event_id
     * @return JsonResponse
     */
    public function list($event_id): JsonResponse
    {
        return response()->json(Record::getRecordsByEventID($event_id));
    }

    /**
     * Returns response with created record object
     *
     * @param $event_id
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function create($event_id, Request $request): Response|JsonResponse
    {
        $params = $request->validate([
            'progress' => ['required', 'numeric'],
            'progress_note' => ['string', 'nullable'],
            'exercise_note' => ['string', 'nullable'],
            'text' => ['string', 'nullable']
        ]);
        $params['event_id'] = $event_id;

        try {
            $record = Record::create($params);
        } catch (QueryException) {
            return response(['message' => trans('messages.recordCreateError')], 409);
        }

        return response()->json(['Record' => $record]);
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
            return response(['message' => trans('messages.recordDoesntExistError')], 404);
        }
        return response()->json(['Record' => $record]);
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
            return response(['message' => trans('messages.recordDoesntExistError')], 404);
        }

        $params = $request->validate([
            'progress' => ['required', 'numeric'],
            'progress_note' => ['string', 'nullable'],
            'exercise_note' => ['string', 'nullable'],
            'text' => ['string', 'nullable']
        ]);

        if(Record::updateRecord($record, $params)) {
            return response()->json(['Record' => $record]);
        }
        else {
            return response(['message' => trans('messages.recordUpdateError')], 409);
        }
    }
}
