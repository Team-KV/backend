<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color'
    ];

    /**
     * Returns category by ID
     *
     * @param $id
     * @return Category|null
     */
    public static function getCategoryByID($id): Category|null
    {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Returns category with all by ID
     *
     * @param $id
     * @return Model|null
     */
    public static function getCategoryWithAllByID($id): Model|null
    {
        return self::with('exercises')->where('id', $id)->first();
    }

    /**
     * Returns collection of categories
     *
     * @return Collection
     */
    public static function getAllCategories(): Collection
    {
        return self::all();
    }

    /**
     * Updates category object with params
     *
     * @param Category $category
     * @param $params
     * @return bool
     */
    public static function updateCategory(Category $category, $params): bool
    {
        try {
            $category->update($params);
            return true;
        } catch(QueryException) {
            return false;
        }
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }
}
