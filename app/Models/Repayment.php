<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Loan;
use App\Models\User;

class Repayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_id',
        'amount',
        'reference',
        'payment_method_id',
        'status',
        'initiator_id'
    ];

    /**
     * Get the loan that owns the Repayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the loan that owns the Repayment
     *
     * @return BelongsTo
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the initiator that owns the Repayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }
}
