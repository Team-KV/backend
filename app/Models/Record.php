<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

class Record extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'progress',
        'progress_note',
        'exercise_note',
        'text',
        'event_id'
    ];


    /**
     * Returns record by ID
     *
     * @param $id
     * @return Model|null
     */
    public static function getRecordByID($id): Record|null {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Returns collection of records for specific event
     *
     * @param $event_id
     * @return Collection
     */
    public static function getRecordsByEventID($event_id): Collection
    {
        return self::all()->where('event_id', $event_id);
    }

    /**
     * Updates record with params
     *
     * @param Record $record
     * @param $params
     * @return bool
     */
    public static function updateRecord(Record $record, $params): bool
    {
        try {
            $record->update($params);
            return true;
        } catch(QueryException) {
            return false;
        }
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
