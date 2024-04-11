<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\LoanType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * Get the product associated with the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
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

    public function calculateOffer(Array $remitaResponseData) : float{
        $averageMonthlySalary = 0;

        if(is_array($remitaResponseData['data']['salaryPaymentDetails'])){
            $salaryPayments = [];

            foreach($remitaResponseData['data']['salaryPaymentDetails'] as $key => $payment){
                $previousMonth = $remitaResponseData['data']['salaryPaymentDetails'][0]['amount'];

                if($key > 0){
                    $previousMonth = $remitaResponseData['data']['salaryPaymentDetails'][$key - 0]['amount'];
                    if(abs($previousMonth - $payment['amount']) > 15000){

                    }
                }else{
                    $salaryPayments[] = $previousMonth;
                }
            }

            $averageMonthlySalary = array_reduce($remitaResponseData['data']['salaryPaymentDetails'], function($total, $item){
                return $total+= $item['amount'];
            });
        }

        $totalAverageSalary = $averageMonthlySalary * $this->tenor;

        $totalLoanOutstanding = 0;

        if(is_array($remitaResponseData['loanHistoryDetails'])){
            $totalLoanOutstanding = array_reduce($remitaResponseData['loanHistoryDetails'], function($total, $item){
                return $total+= $item['outstandingAmount'];
            });
        }

        $totalRepaymentAmount = 0;

        if(is_array($remitaResponseData['loanHistoryDetails'])){
            $totalRepaymentAmount = array_reduce($remitaResponseData['loanHistoryDetails'], function($total, $item){
                return $total+= $item['repaymentAmount'];
            });
        }

        $disposableIncome = $averageMonthlySalary - $totalRepaymentAmount;

        $netOfferAmount = $disposableIncome - 10000;

        $offerAmount = $netOfferAmount - ($netOfferAmount * ($this->loanType->rate/100));

        return $offerAmount;
    }
}
