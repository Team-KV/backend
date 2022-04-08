<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
     * Deletes exerciseTask by task ID
     *
     * @param $task_id
     * @return void
     */
    public static function deleteByTaskID($task_id): void
    {
        DB::table('exercise_task')->where('task_id', $task_id)->delete();
    }
}
