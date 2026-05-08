<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class D365Token extends Model
{
    protected $fillable = ['access_token', 'expires_at', 'generated_by'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function current(): ?static
    {
        return static::where('expires_at', '>', now()->addSeconds(30))->latest()->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function secondsRemaining(): int
    {
        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }
}
