<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Loan;


class ScheduledDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'balance',
        'due_date'
    ];

    /**
     * Get the loan that owns the RepaymentSchedule
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
