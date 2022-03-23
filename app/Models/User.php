<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
        'staff_id',
        'client_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Returns user by ID
     *
     * @param $id
     * @return Model|null
     */
    public static function getUserByID($id): Model|null
    {
        return self::with('staff')->with('client')->where('id', $id)->first();
    }

    /**
     * Returns user by email
     *
     * @param String|null $email
     * @return User|null
     */
    public static function getUserByEmail(String|null $email): User|null
    {
        if($email != null) {
            return self::all()->where('email', $email)->first();
        }
        else {
            return null;
        }
    }

    /**
     * Creates new user for client
     *
     * @param String $email
     * @param $client_id
     * @return bool
     */
    public static function createUser(String $email, $client_id): bool
    {
        $password = Str::random(8);
        $userParams = ['email' => $email,
            'password' => Hash::make($password),
            'role' => 0,
            'staff_id' => null,
            'client_id' => $client_id];
        try {
            User::create($userParams);
        } catch(QueryException) {
            return false;
        }

        //TODO: Send email with credentials

        return true;
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
