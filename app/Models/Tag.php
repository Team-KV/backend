<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;

class Tag extends Model
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
     * Returns tag by ID
     *
     * @param $id
     * @return Tag|null
     */
    public static function getTagByID($id): Tag|null
    {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Returns tag by name
     *
     * @param $name
     * @return Tag|null
     */
    public static function getTagByName($name): Tag|null
    {
        return self::all()->where('name', $name)->first();
    }

    /**
     * Returns collection of tags
     *
     * @return Collection
     */
    public static function getListOfTags(): Collection
    {
        return self::all();
    }

    /**
     * Updates tag with params
     *
     * @param Tag $tag
     * @param $params
     * @return bool
     */
    public static function updateTag(Tag $tag, $params): bool
    {
        try {
            $tag->update($params);
            return true;
        } catch(QueryException) {
            return false;
        }
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }
}
