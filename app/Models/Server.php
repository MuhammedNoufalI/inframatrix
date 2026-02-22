<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->import_uid)) {
                $model->import_uid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'server_name',
        'subscription_name',
        'location',
        'provider',
        'panel',
        'os_name',
        'os_version',
        'status',
        'amc',
        'public_ip',
        'private_ip',
        'is_active',
        'import_uid',
    ];

    protected $casts = [
        'amc' => 'boolean',
    ];
}
