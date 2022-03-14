<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'date_born',
        'sex',
        'personal_information_number',
        'insurance_company',
        'height',
        'weight',
        'phone',
        'street',
        'city',
        'postal_code',
        'sport',
        'past_illnesses',
        'injuries_suffered',
        'diag',
        'note'
    ];

    /**
     * Returns collection of clients
     *
     * @return Collection
     */
    public static function getListOfClients(): Collection
    {
        return self::all();
    }

    /**
     * Returns client by PIN
     *
     * @param String|null $pin
     * @return Client|null
     */
    public static function getClientByPIN(String|null $pin): Client|null
    {
        if($pin != null) {
            return self::all()->where('personal_information_number', $pin)->first();
        }
        else {
            return null;
        }
    }
}
