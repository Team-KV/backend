<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_name',
        'type',
        'exercise_id'
    ];

    /**
     * Removes files by exercise ID
     *
     * @param $exercise_id
     * @return void
     */
    public static function removeFilesByExerciseID($exercise_id): void
    {
        $files = self::all()->where('exercise_id', $exercise_id);
        foreach($files as $file) {
            $file->delete();
        }
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
