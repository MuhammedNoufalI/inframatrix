<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitProvider extends Model
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
        'name',
        'base_url',
        'import_uid',
    ];
}
