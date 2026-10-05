<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type',
    'address1',
    'address2',
    'city',
    'state',
    'zipcode',
    'country_code',
    'is_main',
])]
class UserAddress extends Model
{
    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
