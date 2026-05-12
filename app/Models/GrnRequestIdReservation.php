<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Holds Request IDs allocated for GRN post attempts until a journal row is saved.
 * Prevents reusing the same ID after a failed D365 response when no journal exists yet.
 */
class GrnRequestIdReservation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'company',
        'request_id',
        'created_at',
    ];
}
