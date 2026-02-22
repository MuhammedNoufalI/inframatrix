<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
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
        'integration_type_id',
        'account_name',
        'notes',
        'import_uid',
    ];

    public function integrationType()
    {
        return $this->belongsTo(IntegrationType::class);
    }
}
