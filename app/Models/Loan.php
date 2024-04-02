<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\LoanType;

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

    /**
     * Get the loanType that owns the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loanType(): BelongsTo
    {
        return $this->belongsTo(LoanType::class);
    }

    /**
     * Get the state that owns the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUniqueReference(){
        $ref = '';
        while(strlen($ref) < 1){
            $temp = 'LO'.mt_rand(1000000,10000000);
            if(!Loan::where('reference', $temp)->first()){
                $ref = $temp;
            }
        }
        return $ref;
    }
}
