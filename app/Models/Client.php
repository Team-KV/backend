<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use phpDocumentor\Reflection\Types\Integer;

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
     * Returns client by ID
     *
     * @param $id
     * @return Client|null
     */
    public static function getClientByID($id): Model|null
    {
        return self::with('user')->where('id', $id)->first();
    }

    /**
     * Returns client by PIN
     *
     * @param String|null $pin
     * @return Client|null
     */
    public static function getClientByPIN(String|null $pin): Model|null
    {
        if($pin != null) {
            return self::with('user')->where('personal_information_number', $pin)->first();
        }
        else {
            return null;
        }
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
