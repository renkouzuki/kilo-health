<?php

use App\Http\Controllers\Authentication;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserManagement;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register',[Authentication::class , 'register']);
Route::post('/login',[Authentication::class , 'login']);


Route::middleware('auth:sanctum')->group(function(){
    Route::get('/test',function(Request $req){
        $data = User::with(['role:id,name' , 'role.permissions:id,name'])->paginate(5);
        return response()->json($data);
    })->middleware(['role:admin' , 'permission:view_items']);
    Route::get('/testView',function(){
        return response()->json([
            'msg'=>'hellow world!'
        ]);
    })->middleware(['role:admin']);
});

