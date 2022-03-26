<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Checks if there is free time in calendar for specific staff
     *
     * @param $staff_id
     * @param $start
     * @param $end
     * @return bool
     */
    public static function checkFreeTime($staff_id, $start, $end): bool
    {
        $events = DB::select('SELECT * FROM events
                                    WHERE events.staff_id = ?
                                      AND ((events.start <= ? AND events.end >= ?)
                                               OR (events.start <= ? AND events.end >= ?)
                                               OR (events.start >= ? AND events.end <= ?))',
                                    [$staff_id, $start, $start, $end, $end, $start, $end]);
        return !(count($events) > 0);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
