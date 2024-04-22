<?php

namespace App\Rules;

use App\Constants\Status;
use App\Models\Loan;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RunningLoan implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $runningLoans = Loan::where('user_id', $value)->whereNotIn('status', [Status::COMPLETED, Status::REJECTED])->count();

        if ($runningLoans) {
            $fail('You have an active or processing loan, complete that to reapply.');
        }
    }
}
