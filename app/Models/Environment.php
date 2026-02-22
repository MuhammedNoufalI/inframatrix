<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Environment extends Model
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
        'project_id',
        'type',
        'url',
        'server_id',
        'git_provider_id',
        'repo_url',
        'repo_branch',
        'cicd_configured',
        'cicd_not_configured_reason',
        'checklist_attachment',
        'import_uid',
    ];

    protected $casts = [
        'cicd_configured' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function gitProvider(): BelongsTo
    {
        return $this->belongsTo(GitProvider::class);
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }
}
