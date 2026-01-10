<?php

namespace App\Traits;

use App\Hris\Employees;
use App\MessageBoards;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

trait TQRID
{
    public function getEntryType($type)
    {
        if ($type == 'student') {
            return 10;
        }
//        if ($type == 'employee') {
//            return 11;
//        }
        return 11;
    }

    public function encryptedSecretCode($number)
    {
        $number *= 10;
        return $number / 4;
    }

    private function decryptedSecretCode($number)
    {
        return (($number) / 10) * 4;
    }

    public function encryptedExpiryDate($number)
    {
        $number *= 10;
        return $number / 4;
    }

    private function decryptedExpiryDate($number)
    {
        return (($number) / 10) * 4;
    }

    public function encryptName($name)
    {
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $tl = 0;
        for ($i = 0; $i < strlen($name); $i++) {
            for ($c = 0; $c < strlen($string); $c++) {
                if ($name[$i] == $string[$c]) {
                    $tl += ($c + 15);
                }
            }
        }
        return $tl + 20200607;
    }

    private function decryptName($number, $name)
    {
        $nameNum = $number * 2;
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = [];
        $tl = 0;
        for ($i = 0; $i < strlen($name); $i++) {
            for ($c = 0; $c < strlen($string); $c++) {
                if ($name[$i] == $string[$c]) {
                    $tl += ($c + 15);
                }
            }
        }
        if ($tl == $nameNum) {
            return true;
        } else {
            return false;
        }
    }
}
