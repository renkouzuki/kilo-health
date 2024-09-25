<?php

use App\Http\Controllers\Authentication;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register' , [Authentication::class , 'register']);

Route::get('/test' , function(){
    $data = Permission::pluck('name')->toArray();
    
    $test = [];
    foreach(Role::allRoles() as $role){
        $test[] = $role;
    }

    return response()->json($test);
    
});