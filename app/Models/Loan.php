<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the user that owns the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the referrer that owns the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'reffered_by');
    }

    /**
     * Get the relationshipManager that owns the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relationshipManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'relationship_manager');
    }
}
