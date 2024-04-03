<?php

namespace App\Http\Controllers;

use App\ExternalServices\RemitaService;
use Illuminate\Http\Request;

class ApiTestController
{
    public function getRemitaSalaryHistory()
    {

           $data = [
                "authorisationCode"=> "08989898847",
                "firstName"=> "Teresa",
                "lastName"=> "Stoker",
                "middleName"=> "R",
                "accountNumber"=> "0235012284",
                "bankCode"=> "023",
                "bvn"=> "22222222223",
                "authorisationChannel"=> "USSD"
           ];
     $remita = new RemitaService();
     $response = $remita->getSalaryHistory($data);
     return $response;
    }

}
