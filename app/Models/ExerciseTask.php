<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExerciseTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'feedback',
        'difficulty',
        'repetitions',
        'duration',
        'task_id',
        'exercise_id'
    ];

    /**
     * Returns exercise task by ID
     *
     * @param $id
     */
    public static function getExerciseTaskByID($id)
    {
        return DB::table('exercise_task')->where('id', $id)->first();
    }

    /**
     * Returns collection of exercise tasks by task ID
     *
     * @param $task_id
     * @return Collection
     */
    public static function getExerciseTasksByTaskID($task_id): Collection
    {
        return DB::table('exercise_task')->where('task_id', $task_id)->get();
    }
}
