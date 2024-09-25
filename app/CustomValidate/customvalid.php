<?php

namespace App\CustomValidate;

class CustomValid {
    public static function registerMsg() :array{
        return [
            'email.email' => 'error invalid email address',
            'email.required' => 'error email is required! make sure u fill in'
        ];
    }

    public static function loginMsg():array{
        return [

        ];
    }
}