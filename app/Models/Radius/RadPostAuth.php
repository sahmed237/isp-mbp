<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadPostAuth extends Model
{
    use HasFactory;

    protected $table = 'radpostauth';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'pass',
        'reply',
        'calledstationid',
        'callingstationid',
        'authdate',
        'class',
    ];

    protected $casts = [
        'authdate' => 'datetime',
    ];

    public function getIsSuccessAttribute(): bool
    {
        return str_contains(strtolower($this->reply ?? ''), 'accept');
    }
}
