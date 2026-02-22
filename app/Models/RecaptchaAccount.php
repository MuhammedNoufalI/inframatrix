<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecaptchaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'notes',
    ];
}
