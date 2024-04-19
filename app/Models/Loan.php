<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\LoanType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\ScheduledDeduction;
use App\Models\State;

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
        return $this->belongsTo(User::class, 'reffered_by_id', 'id');
    }

    /**
     * Get the relationshipManager that owns the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function relationshipManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'relationship_manager_id', 'id');
    }

    /**
     * Get the product associated with the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
        return $this->belongsTo(State::class);
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
        $offerCheckResponse = $this->offerCheck($remitaResponseData);
        // dump($disposableIncome, $netOfferAmount, $offerAmount);
        return $offerCheckResponse['offerAmount'];
    }

    public function validityCheck(Array $remitaResponseData){
        $offerCheckResponse = $this->offerCheck($remitaResponseData);
        // dump($disposableIncome, $netOfferAmount, $offerAmount);
        return $offerCheckResponse;
    }

    private function offerCheck($remitaResponseData) : Array{
        $averageMonthlySalary = 0;

        if(is_array($remitaResponseData['data']['salaryPaymentDetails'])){
            $salaryPayments = [];

            foreach($remitaResponseData['data']['salaryPaymentDetails'] as $key => $payment){
                $previousMonth = $remitaResponseData['data']['salaryPaymentDetails'][0]['amount'];

                if($key > 0){
                    $previousMonth = $remitaResponseData['data']['salaryPaymentDetails'][$key - 0]['amount'];
                    if(abs($previousMonth - $payment['amount']) < 15000){
                        $salaryPayments[] = $payment['amount'];
                    }
                }else{
                    $salaryPayments[] = $previousMonth;
                }
            }

            // dd($salaryPayments);

            // dd($averageMonthlySalary);

            $averageMonthlySalary = array_sum($salaryPayments) / count($salaryPayments);
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

        $disposableIncome = $totalAverageSalary - $totalRepaymentAmount;

        $netOfferAmount = $disposableIncome - 10000;

        $offerAmount = $netOfferAmount - ($netOfferAmount * ($this->loanType->rate/100));

        return [
            'offerAmount' => $offerAmount,
            'disposableIncome' => $disposableIncome,
            'monthlyRepayment' => $totalRepaymentAmount/ $this->tenor,
            'averageNetPay' => $averageMonthlySalary,
            'otherDeductions' => 0,
        ];
    }


    /**
     * Get all of the scheduledDeduction for the Loan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scheduledDeduction(): HasMany
    {
        return $this->hasMany(scheduledDeduction::class);
    }
}
