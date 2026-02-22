<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'environment_id',
        'integration_type_id',
        'account_id',
        'value',
    ];

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function integrationType(): BelongsTo
    {
        return $this->belongsTo(IntegrationType::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
