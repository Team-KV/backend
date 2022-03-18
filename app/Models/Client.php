<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
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
        return self::with('user')->with('parent')->with('children')->where('id', $id)->first();
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

    /**
     * Updates client by ID
     *
     * @param $id
     * @param $params
     * @return bool
     */
    public static function updateClientByID($id, $params): bool
    {
        try {
            self::all()->where('id', $id)->update($params);
            return true;
        } catch(QueryException) {
            return false;
        }
    }

    /**
     * Deletes client by ID
     *
     * @param $id
     * @return void
     */
    public static function deleteClientByID($id): void
    {
        self::all()->where('id', $id)->first()->delete();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Client::class, 'client_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
