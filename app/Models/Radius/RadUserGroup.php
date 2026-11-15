<?php

declare(strict_types=1);

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadUserGroup extends Model
{
    use HasFactory;

    protected $table = 'radusergroup';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'groupname',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];
}
