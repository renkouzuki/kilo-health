<?php

use App\Events\testing;
use App\Http\Controllers\Auth\authenticate;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UserManagement;
use App\TestMethod\SwitchMe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [authenticate::class, 'register']);
Route::post('/login', [authenticate::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::put('/update_user', [UserManagement::class, 'UpdateUserInfo']);
    Route::post('/logout', [authenticate::class, 'logout']);

    Route::get('/testView', function () {
        $test = new SwitchMe();
        $result = $test->useSwitch('testMethod');
        return response()->json([
            'msg' => $result
        ]);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagement::class, 'ShowAll'])->middleware(['role:super_admin', 'permission:view_roles']);
        Route::get('/trashed', [UserManagement::class, 'ShowTrashUsers'])->middleware(['role:super_admin', 'permission:view_roles']);
        Route::get('/{user}', [UserManagement::class, 'GetUserDetails'])->middleware(['role:super_admin', 'permission:view_roles']);
        /////////////////// todo fixes this it suppose to work like crud too and don't forget giving it own route -- assign to sopheak
        Route::put('/roles/{role}', [UserManagement::class, 'UpdateRolePermissions'])->middleware(['role:super_admin', 'permission:edit_roles']);
        Route::put('/{user}/role', [UserManagement::class, 'UpdateUserRole'])->middleware(['role:super_admin', 'permission:edit_roles']);
        ///////////////////
        
        Route::delete('/{userId}/soft-delete', [UserManagement::class, 'SoftDeleteUser'])->middleware(['role:super_admin', 'permission:delete_roles']);
        Route::post('/{userId}/restore', [UserManagement::class, 'RestoreUser'])->middleware(['role:super_admin', 'permission:create_roles']);
        Route::delete('/{userId}/force-delete', [UserManagement::class, 'ForceDeleteUser'])->middleware(['role:super_admin', 'permission:delete_roles']);
       
        /////////////////// todo implement a rollback deletes function too -- assign to sopheak
        Route::get('/auditlog/{userId}', [UserManagement::class, 'getAuditLogs'])->middleware(['role:super_admin', 'permission:delete_roles']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::get('/slug/{slug}', [CategoryController::class, 'showBySlug']);
    });

    Route::prefix('topics')->group(function () {
        Route::get('/', [TopicController::class, 'index']);
        Route::post('/', [TopicController::class, 'store']);
        Route::get('/{id}', [TopicController::class, 'show']);
        Route::put('/{id}', [TopicController::class, 'update']);
        Route::delete('/{id}', [TopicController::class, 'destroy']);
        Route::get('/categories/{category}/topics', [TopicController::class, 'getByCategory']);
    });


    Route::prefix('post')->group(function () {});

    Route::prefix('post_views')->group(function () {});

    Route::prefix('upload_media')->group(function () {});

    Route::prefix('site_settings')->group(function () {});
});


//////////////////reverb
Route::post('/msg', function (Request $req) {
    $bruh = $req->message;
    event(new testing($bruh));
    return response()->json([
        'msg' => $bruh
    ]);
});

//Route::get('/read_image',function(){

    //$url = Storage::disk('s3')->temporaryUrl('images/qDzvxaOoXGMQCcxZ1WEXOC4dDDvPO1MQtMc0gYWK.jpg',now()->addHours(5));

    //return response()->json(['url' => $url]);
//});