<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * Returns collection of clients
     *
     * @return Collection
     */
    public static function getListOfClients(): Collection
    {
        return self::all();
    }
}
