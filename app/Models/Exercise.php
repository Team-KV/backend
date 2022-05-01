<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;

class Exercise extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'url',
        'category_id'
    ];

    /**
     * Returns collection of exercises
     *
     * @return Collection
     */
    public static function getAllExercises(): Collection
    {
        return self::all();
    }

    /**
     * Returns exercise with files by ID
     *
     * @param $id
     * @return Model|null
     */
    public static function getExerciseWithFilesByID($id): Model|null
    {
        return self::with('files')->where('id', $id)->first();
    }

    /**
     * Returns exercise by ID
     *
     * @param $id
     * @return Exercise|null
     */
    public static function getExerciseByID($id): Exercise|null
    {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Updates exercise object with params
     *
     * @param Exercise $exercise
     * @param $params
     * @return bool
     */
    public static function updateExercise(Exercise $exercise, $params): bool
    {
        try {
            $exercise->update($params);
            return true;
        } catch (QueryException) {
            return false;
        }
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ExerciseFile::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }
}
