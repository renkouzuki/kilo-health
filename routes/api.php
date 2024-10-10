<?php

use App\Events\testing;
use App\Http\Controllers\Auth\authenticate;
use App\Http\Controllers\UserManagement;
use App\TestMethod\SwitchMe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register',[authenticate::class , 'register']);
Route::post('/login',[authenticate::class , 'login']);


Route::prefix('users')->middleware('auth:sanctum')->group(function(){
    Route::put('/update_user',[UserManagement::class , 'UpdateUserInfo']);
    Route::post('/logout' , [authenticate::class , 'logout']);
    

    Route::get('/testView',function(){
        $test = new SwitchMe();
        $result = $test->useSwitch('testMethod');
        return response()->json([
            'msg'=>$result
        ]);
    });

    Route::get('/', [UserManagement::class, 'ShowAll'])->middleware(['role:super_admin' , 'permission:view_roles']);
    Route::get('/trashed', [UserManagement::class, 'ShowTrashUsers'])->middleware(['role:super_admin' , 'permission:view_roles']);
    Route::get('/{user}', [UserManagement::class, 'GetUserDetails'])->middleware(['role:super_admin' , 'permission:view_roles']);
    Route::put('/roles/{role}', [UserManagement::class, 'UpdateRolePermissions'])->middleware(['role:super_admin' , 'permission:update_roles']);
    Route::put('/{user}/role', [UserManagement::class, 'UpdateUserRole'])->middleware(['role:super_admin' , 'permission:update_roles']);
    Route::delete('/{userId}/soft-delete', [UserManagement::class, 'SoftDeleteUser'])->middleware(['role:super_admin' , 'permission:delete_roles']);
    Route::post('/{userId}/restore', [UserManagement::class, 'RestoreUser'])->middleware(['role:super_admin' , 'permission:create_roles']);
    Route::delete('/{userId}/force-delete', [UserManagement::class, 'ForceDeleteUser'])->middleware(['role:super_admin' , 'permission:delete_roles']);
    Route::get('/auditlog/{userId}' , [UserManagement::class , 'getAuditLogs'])->middleware(['role:super_admin' , 'permission:delete_roles']);
});


//////////////////reverb
Route::post('/msg',function(Request $req){
    $bruh = $req->message;
    event(new testing($bruh));
    return response()->json([
        'msg'=>$bruh
    ]);
});