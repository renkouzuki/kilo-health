<?php

namespace App\CustomValidation;

class CustomValue{
    public static function RegisterMsg(){
        return [
            
        ];
    }

    public static function LoginMsg(){
        return [
            'email.required'=>'Oops! check your email again.',
            'email.email'=>'Oops! check your email again',
            'password.required'=>'Password is required :3'
        ];
    }
}