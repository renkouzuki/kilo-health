<?php

namespace App\TestMethod;

class Alias{
    
    public function getMethod(){
        return [
            'testMethod' => [$this , 'getBasicHellowWorld']
        ];
    }

    public function getBasicHellowWorld(){
        return 'Hello World!';
    }
}