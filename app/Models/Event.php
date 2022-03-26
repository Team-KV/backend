<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start',
        'end',
        'note',
        'event_type_id',
        'client_id',
        'staff_id'
    ];

    /**
     * Returns event by ID
     *
     * @param $id
     * @return Event|null
     */
    public static function getEventByID($id): Event|null
    {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Returns event by ID with all information
     *
     * @param $id
     * @return Model|null
     */
    public static function getEventWithAllByID($id): Model|null
    {
        return self::with('eventType')->
        with('client')->
        with('staff')->
        where('id', $id)->
        first();
    }

    /**
     * Returns collection of events by datetime and period
     *
     * @param $dateTime
     * @param String $period
     * @return Collection
     */
    public static function getAllEvents($dateTime, String $period = 'day'): Collection
    {
        $events = Collection::empty();
        switch($period) {
            case 'day':
                $events = self::with('eventType')->
                with('client')->
                with('staff')->
                whereBetween('start',
                    [date('Y-m-d', strtotime($dateTime)).' 00:00:01',
                        date('Y-m-d', strtotime($dateTime)).' 23:59:59'])->
                whereBetween('end',
                    [date('Y-m-d', strtotime($dateTime)).' 00:00:01',
                        date('Y-m-d', strtotime($dateTime)).' 23:59:59'])->
                get();
                break;
            case 'week':
                $monday = date("Y-m-d", strtotime('monday this week', strtotime($dateTime)));
                $sunday = date("Y-m-d", strtotime('sunday this week', strtotime($dateTime)));
                $events = self::with('eventType')->
                with('client')->
                with('staff')->
                whereBetween('start',
                    [$monday.' 00:00:01',
                        $sunday.' 23:59:59'])->
                whereBetween('end',
                    [$monday.' 00:00:01',
                        $sunday.' 23:59:59'])->
                get();
                break;
            case 'month':
                $first = date("Y-m-d", strtotime('first day of this month', strtotime($dateTime)));
                $last = date("Y-m-d", strtotime('last day of this month', strtotime($dateTime)));
                $events = self::with('eventType')->
                with('client')->
                with('staff')->
                whereBetween('start',
                    [$first.' 00:00:01',
                        $last.' 23:59:59'])->
                whereBetween('end',
                    [$first.' 00:00:01',
                        $last.' 23:59:59'])->
                get();
                break;
        }
        return $events;
    }

    /**
     * Updates event with params
     *
     * @param Event $event
     * @param $params
     * @return bool
     */
    public static function updateEvent(Event $event, $params): bool
    {
        try {
            $event->update($params);
            return true;
        } catch(QueryException) {
            return false;
        }
    }

    /**
     * Checks if there is free time in calendar for specific staff
     *
     * @param $staff_id
     * @param $start
     * @param $end
     * @return bool
     */
    public static function checkFreeTime($staff_id, $start, $end, $event_id = null): bool
    {
        if($event_id == null) {
            $events = DB::select('SELECT * FROM events
                                    WHERE events.staff_id = ?
                                      AND ((events.start <= ? AND events.end >= ?)
                                               OR (events.start <= ? AND events.end >= ?)
                                               OR (events.start >= ? AND events.end <= ?))',
                [$staff_id, $start, $start, $end, $end, $start, $end]);
        }
        else {
            $events = DB::select('SELECT * FROM events
                                    WHERE events.staff_id = ? AND events.id <> ?
                                      AND ((events.start <= ? AND events.end >= ?)
                                               OR (events.start <= ? AND events.end >= ?)
                                               OR (events.start >= ? AND events.end <= ?))',
                [$staff_id, $event_id, $start, $start, $end, $end, $start, $end]);
        }
        return !(count($events) > 0);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
