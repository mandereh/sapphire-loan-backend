<?php

namespace App\Http\Controllers;

use App\ExternalServices\DigisignService;
use App\ExternalServices\RemitaService;
use Illuminate\Http\Request;

class ApiTestController
{
    private RemitaService $remitaService;
    private DigisignService $digisignService;
    private String $authorizationCode;
    /**
     * @param RemitaService $remitaService
     */
    public function __construct()
    {
        $this->remitaService = new RemitaService();
        $this->digisignService = new DigisignService();
        $this->authorizationCode = uuid_create();
    }


    public function getRemitaSalaryHistory()
    {

           $data = [
                "authorisationCode"=> $this->authorizationCode,
                "firstName"=> "Teresa",
                "lastName"=> "Stoker",
                "middleName"=> "R",
                "accountNumber"=> "0235012284",
                "bankCode"=> "023",
                "bvn"=> "22222222223",
                "authorisationChannel"=> "USSD"
           ];

     return $this->remitaService->getSalaryHistory($data);

    }
    public function getRemitaSalaryHistoryByPhonenumber()
    {

           $data = [
                "authorisationCode"=> $this->authorizationCode,
                "firstName"=> "Daerego",
                "lastName"=> "Braide",
                "middleName"=> "R",
                "accountNumber"=> "0235012284",
                "phoneNumber"=> "07068541504",
                "bankCode"=> "023",
                "bvn"=> "22222222223",
                "authorisationChannel"=> "USSD"
           ];

     return $this->remitaService->getSalaryHistory($data);

    }

    public function loanDisburstmentNotificationController()
    {
        $data = [
            "customerId" => "1366",
            "authorisationCode" => "1067808",
            "authorisationChannel" => "USSD",
            "phoneNumber" => "08154567478",
            "accountNumber" => "0235012284",
            "currency" => "NGN",
            "loanAmount" => "20000",
            "collectionAmount" => "4120",
            "disbursementDate" => "04-04-2024 01:09:25+0000",
            "disbursementDate" => "04-04-2024 01:09:25+0000",
            "totalCollectionAmount" => "20600",
            "numberOfRepayments" => "5",
            "bankCode" => "023"
        ];

        return $this->remitaService->loanDisbursementNotification($data);
    }

    public function mandateHistoryController()
    {
        $data = [
            "authorisationCode" => "1067808",
            "customerId" => "1366",
            "mandateReference" => "180798011994"
        ];

        return $this->remitaService->mandateHistory($data);
    }

    public function stopLoanCollectionController()
    {
        $data = [
            "authorisationCode" => "1067808",
            "customerId" => "1366",
            "mandateReference" => "180798011994"
        ];

        return $this->remitaService->stopLoanCollection($data);
    }

    public function transformTemplate()
    {
        return $this->digisignService->transformTemplate();
    }

}
