<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Returns collection of records for specific event
     *
     * @param $event_id
     * @return Collection
     */
    public static function getRecordsByEventID($event_id): Collection
    {
        return self::all()->where('event_id', $event_id);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
