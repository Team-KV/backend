<?php

namespace App\Models;

use App\Helpers\GraphData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;

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
        'contact_email',
        'street',
        'city',
        'postal_code',
        'sport',
        'past_illnesses',
        'injuries_suffered',
        'anamnesis',
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
    public static function getClientByID($id): Client|null
    {
        return self::all()->where('id', $id)->first();
    }

    public static function getClientWithAllByID($id): Model|null
    {
        return self::with('user')->
            with('parent')->
            with('children')->
            with('events')->
            with('attachments')->
            where('id', $id)->
            first();
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
            self::all()->where('id', $id)->first()->update($params);
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

    /**
     * Verify if PIN is valid for Czech republic
     *
     * @param String $pin
     * @return bool
     */
    public static function verifyPIN(String $pin): bool
    {
        //Format check
        if(!preg_match('#^\s*(\d\d)(\d\d)(\d\d)(\d\d\d)(\d?)\s*$#', $pin, $matches)) {
            return false;
        }

        list(, $year, $month, $day, $ext, $c) = $matches;

        //until 1954 there were only 9 digit PINs, can't check
        if($c === '') {
            return $year < 54;
        }

        //Control digit check
        $mod = ($year.$month.$day.$ext) % 11;
        if($mod === 10) {
            $mod = 0;
        }
        if($mod !== (int)$c) {
            return false;
        }

        //Set right year
        $year += $year < 54 ? 2000 : 1900;

        //For women, it can be added to the month 20, 50 or 70
        if($month > 70 && $year > 2003) {
            $month -= 70;
        }
        else if($month > 50) {
            $month -= 50;
        }
        else if($month > 20 && $year > 2003) {
            $month -= 20;
        }

        if(!checkdate($month, $day, $year)) {
            return false;
        }

        return true;
    }

    /**
     * Returns graph data from records for specific client in array
     *
     * @param $id
     * @return array
     */
    public static function getGraphData($id): array
    {
        $data = array();
        $progress = 0;
        $events = Event::getEventsByClientID($id);
        foreach($events as $event) {
            $records = Record::getRecordsByEventID($event->id);
            foreach($records as $record) {
                $progress += $record->progress;
                $graphData = new GraphData($event->start, $progress);
                array_push($data, $graphData);
            }
        }

        return $data;
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

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
