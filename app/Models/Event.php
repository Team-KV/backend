<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
            with('record')->
            with('task')->
            where('id', $id)->
            first();
    }

    /**
     * Returns sorted events for specific client
     *
     * @param $client_id
     * @return Collection
     */
    public static function getEventsByClientID($client_id): Collection
    {
        return self::all()->
            where('client_id', $client_id)->
            sortBy('start');
    }

    /**
     * Returns next event by client ID
     *
     * @param $client_id
     * @return Event|null
     */
    public static function getNextEventByClientID($client_id): Event|null
    {
        return self::all()->
            where('start', '>=', date('Y-m-d H:i:s', strtotime(NOW()->toString())))->
            where('client_id', $client_id)->
            first();
    }

    /**
     * Returns collection of events
     *
     * @return Collection
     */
    public static function getAllEvents(): Collection
    {
        return self::all();
    }

    /**
     * Returns collection of events by datetime and period
     *
     * @param $dateTime
     * @param String $period
     * @return Collection
     */
    public static function getAllEventsByFilter($dateTime, String $period = 'day'): Collection
    {
        $events = Collection::empty();
        switch($period) {
            case 'day':
                $events = self::all()->
                whereBetween('start',
                    [date('Y-m-d', strtotime($dateTime)).' 00:00:00',
                        date('Y-m-d', strtotime($dateTime)).' 23:59:59'])->
                whereBetween('end',
                    [date('Y-m-d', strtotime($dateTime)).' 00:00:00',
                        date('Y-m-d', strtotime($dateTime)).' 23:59:59']);
                break;
            case 'week':
                $monday = date("Y-m-d", strtotime('monday this week', strtotime($dateTime)));
                $sunday = date("Y-m-d", strtotime('sunday this week', strtotime($dateTime)));
                $events = self::all()->
                whereBetween('start',
                    [$monday.' 00:00:00',
                        $sunday.' 23:59:59'])->
                whereBetween('end',
                    [$monday.' 00:00:00',
                        $sunday.' 23:59:59']);
                break;
            case 'month':
                $first = date("Y-m-d", strtotime('first day of this month', strtotime($dateTime)));
                $last = date("Y-m-d", strtotime('last day of this month', strtotime($dateTime)));
                $events = self::all()->
                whereBetween('start',
                    [$first.' 00:00:00',
                        $last.' 23:59:59'])->
                whereBetween('end',
                    [$first.' 00:00:00',
                        $last.' 23:59:59']);
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
     * Deletes event with all attached objects
     *
     * @param Event $event
     * @return void
     */
    public static function deleteEvent(Event $event): void
    {
        if($event->task != null) {
            Task::removeExercisesFromTask($event->task->id);
            $event->task->delete();
        }

        if($event->record != null) {
            $event->record->delete();
        }

        $event->delete();
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
                                      AND ((events.start <= ? AND events.end > ?)
                                               OR (events.start < ? AND events.end >= ?)
                                               OR (events.start >= ? AND events.end <= ?))',
                [$staff_id, $start, $start, $end, $end, $start, $end]);
        }
        else {
            $events = DB::select('SELECT * FROM events
                                    WHERE events.staff_id = ? AND events.id <> ?
                                      AND ((events.start <= ? AND events.end > ?)
                                               OR (events.start < ? AND events.end >= ?)
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

    public function record(): HasOne
    {
        return $this->hasOne(Record::class);
    }

    public function task(): HasOne
    {
        return $this->hasOne(Task::class);
    }
}
