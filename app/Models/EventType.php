<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;

class EventType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name'
    ];

    /**
     * Returns collection of event types
     *
     * @return Collection
     */
    public static function getListOfTypes(): Collection
    {
        return self::all();
    }

    /**
     * Returns event type by ID
     *
     * @param $id
     * @return EventType|null
     */
    public static function getEventTypeByID($id): EventType|null
    {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Returns event type by name
     *
     * @param $name
     * @return EventType|null
     */
    public static function getEventTypeByName($name): EventType|null
    {
        return self::all()->where('name', $name)->first();
    }

    /**
     * Updates event type
     *
     * @param EventType $eventType
     * @param $params
     * @return bool
     */
    public static function updateEventType(EventType $eventType, $params): bool
    {
        try {
            $eventType->update($params);
            return true;
        } catch(QueryException) {
            return false;
        }
    }

    public function event(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
