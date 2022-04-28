<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_name',
        'type',
        'url',
        'client_id'
    ];

    /**
     * Returns attachment file
     *
     * @param $id
     * @return Attachment|null
     */
    public static function getFileByID($id): Attachment|null
    {
        return self::all()->where('id', $id)->first();
    }

    /**
     * Removes files by client ID
     *
     * @param $client_id
     * @return void
     */
    public static function removeFilesByClientID($client_id): void
    {
        $files = self::all()->where('client_id', $client_id);
        foreach($files as $file) {
            $file->delete();
        }
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
