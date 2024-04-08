<?php

namespace App\ExternalServices\Helpers;


use App\ExternalServices\DigisignService;

class DigisignHelper
{
    public static function templateDetails($loan = null, $email = null)
    {
        $recipient_id = '';
        $loan_product = '';
        $accountNumber = '';
        $houseAddress = '';
        $processingTime = '';
        $loanReason = '';
        $loanAmount = '';
        $dateOfDisbursement = '';
        $actualTenor = '';
        $insurance = 4;
        $disbursementFees = 5;
        $totalInterest = 1000.000;
        $firstRepaymentDate = '';
        $maturityDate = '';



        $digiSignData = [];
        $digiSignRecipient = [];
        $digiSignRecipientsFillables = [];
        $digiSignMessage = [];

        $digiSignRecipient['private_message'] = "Kindly follow the link below (click on the button below) to view / accept the loan offer
         terms and conditions to complete the application process.";
        $digiSignRecipient['email'] = "phronesis4xt@gmail.com";
        $digiSignRecipient['name'] = "Daerego Braide";
        $digiSignRecipient['id'] = "9aa69bec-cc17-4393-9ce0-c0496817d9f0";

        $digiSignMessage['subject'] = "Agreed loan offer and Terms and  Conditions";
        $digiSignMessage['body'] = "Congratulations  \n Your  Application has been approved for the sum
        of N payable over a period of  months.\n\n\n Please note that the loan application process to disbursement is done electronically.";

        $digiSignRecipientFillables['role'] = "";

//        $digiSignRecipientFillables['loan_account_number'] = $accountNumber;
//        $digiSignRecipientFillables['customer_address'] = $houseAddress;
//        $digiSignRecipientFillables['processing_date'] = $processingTime;
//        $digiSignRecipientFillables['loan_type'] = ucwords(strtolower($loanReason));
//        $digiSignRecipientFillables['disbursement_amount'] = "N";
////        $digiSignRecipientFillables['disbursement_amount'] = "N" . number_format($loanAmount,2);
//        $digiSignRecipientFillables['disbursement_date'] = $dateOfDisbursement;
//        $digiSignRecipientFillables['loan_tenor'] = "$actualTenor Days";
//        $digiSignRecipientFillables['monthly_repayment'] = "N";
////        $digiSignRecipientFillables['monthly_repayment'] = "N" . number_format($loan->monthlyRepayment,2);
//        $digiSignRecipientFillables['interest_rate'] = "0.167% per day";
//        $digiSignRecipientFillables['insurance_disbursement_fee'] = "N";
////        $digiSignRecipientFillables['insurance_disbursement_fee'] = "N" . number_format(($insurance + $disbursementFees),2);
//        $digiSignRecipientFillables['late_repayment_fee'] = "There will be a fee of 0.167% flat per day in addition to the agreed rate";
////        $digiSignRecipientFillables['late_repayment_fee2'] = "of interest of any missed repayments.";
//        $digiSignRecipientFillables['collateral'] = "NIL";
//        $digiSignRecipientFillables['total_interest'] = "N";
////        $digiSignRecipientFillables['total_interest'] = "N" . number_format($totalInterest,2);
//        $digiSignRecipientFillables['repayment_frequency'] = "Monthly";
//        $digiSignRecipientFillables['number_of_repayments'] = "";
//        $digiSignRecipientFillables['first_repayment_date'] = $firstRepaymentDate;
//        $digiSignRecipientFillables['maturity_date'] = $maturityDate;
//        $digiSignRecipientFillables['repayment_amount'] = "N";
////        $digiSignRecipientFillables['repayment_amount'] = "N" . number_format($loan->monthlyRepayment * $loan->tenor,2);
//        $digiSignRecipientFillables['repayment_schedule'] = "Please refer to the repayment schedule on the last page.";
//        $digiSignRecipientFillables['repayment_date'] = "Last day of each month for the duration of the loan.";
//        $digiSignRecipientFillables['borrower'] = "";
//        $digiSignRecipientFillables['borrower_employer'] = "";
//        $digiSignRecipientFillables['due_date1'] = "";
//        $digiSignRecipientFillables['due_date2'] = "";
//        $digiSignRecipientFillables['due_date3'] = "";
//        $digiSignRecipientFillables['due_date4'] = "";
//        $digiSignRecipientFillables['due_date5'] = "";
//        $digiSignRecipientFillables['due_date6'] = "";
//        $digiSignRecipientFillables['repayment_date1'] = "";
//        $digiSignRecipientFillables['repayment_date2'] = "";
//        $digiSignRecipientFillables['repayment_date3'] = "";
//        $digiSignRecipientFillables['repayment_date4'] = "";
//        $digiSignRecipientFillables['repayment_date5'] = "";
//        $digiSignRecipientFillables['repayment_date6'] = "";
//        $digiSignRecipientFillables['signed_text'] = "";
//        $digiSignRecipientFillables['disclaimer'] = "QR code represents the signature $loan->firstname $loan->lastname. Please scan for more information";

        $digiSignRecipient['fillable'] = $digiSignRecipientFillables;
        $digiSignData['recipients'][0] = $digiSignRecipient;
        $digiSignData['message'] = $digiSignMessage;


        return $digiSignData;

    }

}
