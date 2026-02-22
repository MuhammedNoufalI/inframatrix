<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
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
        'status',
        'notes',
        'import_uid',
    ];

    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }
}
