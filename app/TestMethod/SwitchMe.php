<?php

namespace App\TestMethod;

class SwitchMe{

    private $alias;

    public function __construct() {
        $this->alias = new Alias();
    }

    public function useSwitch(string $methodName , ...$params){
        $methods = $this->alias->getMethod();

        if(!isset($methods[$methodName])){
            return 'Method not found';
        }

        $method = $methods[$methodName];

        return call_user_func_array($method , $params);
    }

        
}