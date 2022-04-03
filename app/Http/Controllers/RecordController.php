<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    /**
     * Returns collection of records for specific event in JSON
     *
     * @param $event_id
     * @return JsonResponse
     */
    public function list($event_id): JsonResponse
    {
        return response()->json(Record::getRecordsByEventID($event_id));
    }
}
