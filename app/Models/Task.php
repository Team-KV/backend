<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'text',
        'is_active',
        'client_id',
        'event_id'
    ];

    /**
     * Returns task by ID
     *
     * @param $id
     * @return Task|null
     */
    public static function getTaskByID($id): Task|null
    {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Returns task with exercises by ID
     *
     * @param $id
     * @return Model|null
     */
    public static function getTaskWithExercisesByID($id): Model|null
    {
        return self::with('exercises')->where('id', $id)->first();
    }

    /**
     * Returns collection of tasks by event ID
     *
     * @param $event_id
     * @return Collection
     */
    public static function getAllTasksByEventID($event_id): Collection
    {
        return self::all()->where('event_id', $event_id);
    }

    /**
     * Updates task object with params
     *
     * @param Task $task
     * @param $params
     * @return bool
     */
    public static function updateTask(Task $task, $params): bool
    {
        try {
            $task->update($params);
            return true;
        } catch (QueryException) {
            return false;
        }
    }

    /**
     * Removes exercises from task by ID
     *
     * @param $id
     * @return void
     */
    public static function removeExercisesFromTask($id): void
    {
        self::getTaskByID($id)->exercises()->detach();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class)->withPivot('id', 'feedback', 'difficulty', 'repetitions', 'duration', 'task_id', 'exercise_id');
    }
}
