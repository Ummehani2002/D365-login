<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        return $row ? $row->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function d365Creds(): array
    {
        $keys = ['d365_tenant_id', 'd365_client_id', 'd365_client_secret', 'd365_scope', 'd365_base_url'];
        $rows = static::whereIn('key', $keys)->pluck('value', 'key');

        return [
            'd365_tenant_id'     => $rows['d365_tenant_id'] ?? null,
            'd365_client_id'     => $rows['d365_client_id'] ?? null,
            'd365_client_secret' => $rows['d365_client_secret'] ?? null,
            'd365_scope'         => $rows['d365_scope'] ?? null,
            'd365_base_url'      => $rows['d365_base_url'] ?? null,
        ];
    }
}
