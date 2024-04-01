<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
            'name',
            'phone_number',
            'account_number',
            'ippis_number',
            'organization_name',
            'state_name',
            'city_name',
    ];

    /**
     * Get the salesOfficer that owns the Lead
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


}
