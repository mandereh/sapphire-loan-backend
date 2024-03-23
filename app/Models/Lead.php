<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

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
