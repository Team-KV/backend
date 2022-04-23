<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    /**
     * Returns first staff record
     *
     * @return Staff|null
     */
    public static function getStaff(): Staff|null
    {
        return self::all()->first();
    }
}
