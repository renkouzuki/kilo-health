<?php

use App\Events\testing;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\authenticate;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PemrissionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\postPhotosController;
use App\Http\Controllers\PostViewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UploadMediaController;
use App\Http\Controllers\UserManagement;
use App\Models\categorie;
use App\TestMethod\SwitchMe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::post('/register', [authenticate::class, 'register']);
Route::post('/login', [authenticate::class, 'login']);
Route::get('/allsettings' , [SiteSettingController::class , 'homepageSettings']);
Route::middleware('auth:sanctum')->group(function () {
    ///// public routes
    Route::put('/update_user', [UserManagement::class, 'UpdateUserInfo']);
    Route::get('/profile',[UserManagement::class , 'getUserDetails']);
    Route::post('/logout', [authenticate::class, 'logout']);
    Route::get('posts', [PostController::class, 'getPublished'])->middleware('permission:view_items');
    Route::get('popular/posts', [PostController::class , 'popularPosts'])->middleware('permission:view_items');
    Route::get('posts/{id}', [PostController::class, 'publicShow'])->middleware('permission:view_items');
    Route::post('posts/{postId}/view', [PostViewController::class, 'recordView'])->middleware('permission:view_items');
    Route::post('/{id}/like', [PostController::class, 'like'])->middleware('permission:view_items');
    Route::delete('/{id}/like', [PostController::class, 'unlike'])->middleware('permission:view_items');
   
    Route::get('/popular/topics' , [TopicController::class , 'popularTopics'])->middleware('permission:view_items');
    
    Route::get('/setting/{key}' , [SiteSettingController::class , 'homepageSetting'])->middleware('permission:view_items');
    Route::get('/related_posts/{postId}' , [PostController::class , 'getRelatedPosts'])->middleware('permission:view_items');
    Route::get('/popularCategory' , [CategoryController::class , 'getPopularCategory'])->middleware('permission:view_items');

    Route::get('/testView', function () {
        $test = new SwitchMe();
        $result = $test->useSwitch('testMethod');
        return response()->json([
            'msg' => $result
        ]);
    });

    Route::get('/dashboard' , [AnalyticsController::class , 'getDashboardAnalytics'])->middleware('role:super_admin|admin');
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagement::class, 'ShowAll'])->middleware(['role:super_admin|admin', 'permission:view_users']);
        Route::get('/trashed', [UserManagement::class, 'ShowTrashUsers'])->middleware(['role:super_admin', 'permission:view_delete_users']);
        Route::get('/{user}', [UserManagement::class, 'GetUserDetails'])->middleware(['role:super_admin|admin', 'permission:view_users']);
        Route::delete('/{userId}/soft-delete', [UserManagement::class, 'SoftDeleteUser'])->middleware(['role:super_admin', 'permission:delete_users']);
        Route::post('/{userId}/restore', [UserManagement::class, 'RestoreUser'])->middleware(['role:super_admin', 'permission:restore_users']);
        Route::delete('/{userId}/force-delete', [UserManagement::class, 'ForceDeleteUser'])->middleware(['role:super_admin', 'permission:force_delete_users']);
        /////// implementing audit log db roll back for accidentally delete data
        Route::get('/auditlog/{userId}', [UserManagement::class, 'getAuditLog'])->middleware(['role:super_admin', 'permission:view_log']);
        Route::post('/{userId}/rollbackData', [UserManagement::class, 'rollbackDelete'])->middleware(['role:super_admin']);
    });

    Route::prefix('roles')->group(function(){
        Route::get('/trashed', [RoleController::class, 'displayTrashed'])->middleware(['role:super_admin', 'permission:view_delete_roles']);
        Route::get('/', [RoleController::class, 'index'])->middleware(['role:super_admin|admin', 'permission:view_roles']);
        Route::get('/{id}', [RoleController::class, 'show'])->middleware(['role:super_admin|admin', 'permission:view_roles']);
        Route::post('/', [RoleController::class, 'store'])->middleware(['role:super_admin', 'permission:create_roles']);
        Route::put('/{id}', [RoleController::class, 'update'])->middleware(['role:super_admin', 'permission:update_roles']);
        Route::put('/{id}/permissions', [UserManagement::class, 'UpdateRolePermissions'])->middleware(['role:super_admin', 'permission:edit_roles']);
        Route::put('/users/{id}/role', [UserManagement::class, 'UpdateUserRole'])->middleware(['role:super_admin', 'permission:edit_users']);
        Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware(['role:super_admin', 'permission:delete_roles']);
        Route::post('/{id}/restore', [RoleController::class, 'restore'])->middleware(['role:super_admin', 'permission:restore_roles']);
        Route::delete('/{id}/force', [RoleController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_roles']);
        
    });
    
    Route::prefix('permissions')->group(function () {
        Route::get('/trashed', [PemrissionController::class, 'displayTrashed'])->middleware(['role:super_admin', 'permission:view_delete_permissions']);
        Route::get('/', [PemrissionController::class, 'index'])->middleware(['role:super_admin', 'permission:view_permissions']);
        Route::get('/{id}', [PemrissionController::class, 'show'])->middleware(['role:super_admin', 'permission:view_permissions']);
        Route::post('/', [PemrissionController::class, 'store'])->middleware(['role:super_admin', 'permission:create_permissions']);
        Route::put('/{id}', [PemrissionController::class, 'update'])->middleware(['role:super_admin', 'permission:update_permissions']);
        Route::delete('/{id}', [PemrissionController::class, 'destroy'])->middleware(['role:super_admin', 'permission:delete_permissions']);
        Route::post('/{id}/restore', [PemrissionController::class, 'restore'])->middleware(['role:super_admin', 'permission:restore_permisssions']);
        Route::delete('/{id}/force', [PemrissionController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_permissions']);
    });

    ////////////// heng visal routes
    Route::prefix('categories')->group(function () {
        Route::get('/trashed', [CategoryController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/', [CategoryController::class, 'index'])->middleware('permission:view_items');
        Route::post('/', [CategoryController::class, 'store'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        Route::get('/{id}', [CategoryController::class, 'show'])->middleware('permission:view_items');
        Route::put('/{id}', [CategoryController::class, 'update'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::get('/slug/{slug}', [CategoryController::class, 'showBySlug'])->middleware('permission:view_items');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('/{id}/restore', [CategoryController::class, 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [CategoryController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']); 
    });

    Route::prefix('topics')->group(function () {
        Route::get('/trashed', [TopicController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/', [TopicController::class, 'index'])->middleware('permission:view_items');
        Route::post('/', [TopicController::class, 'store'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        Route::get('/{id}', [TopicController::class, 'show'])->middleware('permission:view_items');
        Route::put('/{id}', [TopicController::class, 'update'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::delete('/{id}', [TopicController::class, 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('/{id}/restore', [TopicController::class, 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [TopicController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']);
        Route::get('/categories/{category}/topics', [TopicController::class, 'getByCategory'])->middleware('permission:view_items');
    });

    /////////////// jung kook routes and lay vila routes
    Route::prefix('post')->group(function () {
        Route::get('trashed', [PostController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/', [PostController::class, 'index'])->middleware(['role:super_admin|admin', 'permission:view_items']);
        Route::post('/', [PostController::class, 'store'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        Route::get('{id}', [PostController::class, 'show'])->middleware('permission:view_items');
        Route::get('{id}/post_photos', [PostController::class, 'getPostPhotosById'])->middleware('role:super_admin|admin|arthur');
        Route::put('/{id}', [PostController::class, 'update'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::delete('{id}', [PostController::class, 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('{id}/restore', [PostController::class, 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('{id}/force', [PostController::class, 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']);
        Route::post('{id}/publish', [PostController::class, 'publish'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
        Route::post('{id}/unpublish', [PostController::class, 'unpublish'])->middleware(['role:super_admin|admin|arthur', 'permission:update_items']);
    });

    Route::prefix('post_views')->group(function () {
        //// need pagination
        Route::get('posts/{postId}/views', [PostViewController::class, 'getViewsByPost'])->middleware(['role:super_admin|admin|arthur', 'permission:view_items']);
        Route::get('users/{userId}/views', [PostViewController::class, 'getViewsByUser'])->middleware(['role:super_admin|admin', 'permission:view_users']);
        Route::get('posts/{postId}/check-view', [PostViewController::class, 'checkUserViewedPost'])->middleware('permission:view_items');
        Route::get('posts/{postId}/view-count' , [PostViewController::class , 'getViewCount'])->middleware('permission:view_items');
    });

    Route::prefix('upload_media')->group(function () {
        Route::get('/', [UploadMediaController::class, 'index'])->middleware(['role:super_admin|admin', 'permission:view_items']);
        Route::get('/trashed', [UploadMediaController::class, 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::post('/', [UploadMediaController::class, 'upload'])->middleware(['role:super_admin|admin|arthur', 'permission:create_items']);
        //Route::get('/{id}/media', [UploadMediaController::class, 'getMediaByPost'])->middleware('permission:view_items');
        Route::delete('/{id}', [UploadMediaController::class, 'deleteMedia'])->middleware(['role:super_admin|admin|arthur', 'permission:delete_items']);
        Route::get('/{id}', [UploadMediaController::class, 'getMedia'])->middleware('permission:view_items');
        Route::get('/{id}/posts', [UploadMediaController::class, 'getPostByMediaId'])->middleware(['role:super_admin|admin|arthur']);
        Route::post('/{id}/restore', [UploadMediaController::class, 'restored'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force', [UploadMediaController::class, 'forceDeleted'])->middleware(['role:super_admin', 'permission:force_delete_items']);
    });

    //// retrieve post thumbnail click having more photos daetail routes
    Route::prefix('post_photos')->group(function(){
        Route::get('/trashed' , [postPhotosController::class , 'trashed'])->middleware(['role:super_admin|admin', 'permission:view_delete_items']);
        Route::get('/{id}/photos' , [postPhotosController::class , 'index'])->middleware(['permission:view_items']);
        Route::post('/{id}' , [postPhotosController::class , 'store'])->middleware(['role:super_admin|admin', 'permission:create_items']);
        Route::get('/{id}' , [postPhotosController::class , 'show'])->middleware('permission:view_items');
        Route::put('/{id}' , [postPhotosController::class , 'update'])->middleware(['role:super_admin|admin', 'permission:update_items']);
        Route::delete('/{id}' , [postPhotosController::class , 'destroy'])->middleware(['role:super_admin|admin', 'permission:delete_items']);
        Route::post('/{id}/restore' , [postPhotosController::class , 'restore'])->middleware(['role:super_admin|admin', 'permission:restore_items']);
        Route::delete('/{id}/force' , [postPhotosController::class , 'forceDelete'])->middleware(['role:super_admin', 'permission:force_delete_items']);
    });

    Route::prefix('site_settings')->group(function () {
        Route::get('/', [SiteSettingController::class, 'index'])->middleware(['role:super_admin', 'permission:view_items']);
        Route::get('/{key}', [SiteSettingController::class, 'show'])->middleware(['role:super_admin', 'permission:view_items']);
        Route::put('/{key}', [SiteSettingController::class, 'update'])->middleware(['role:super_admin', 'permission:update_items']);
        Route::post('/', [SiteSettingController::class, 'store'])->middleware(['role:super_admin', 'permission:create_items']);
        Route::delete('/{key}', [SiteSettingController::class, 'destroy'])->middleware(['role:super_admin', 'permission:delete_items']);
    });
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

Route::get('/test' , function(){
    return [
        'data' => Categorie::withCount(['posts as total_likes' => function($query) {
            $query->select(DB::raw('sum(likes)'));
        }])->withSum('posts', 'views')
        ->with(['posts'=>function($query){
            $query->select('id','title','category_id',DB::raw('sum(views) as total_views'))
            ->where('title' , 'chainsaw man is awsome <3')
            ->with('views')->groupBy('id','title','category_id');
        }])->select(DB::raw('min(id) as id') , 'name')->groupBy('name')->get()
    ];
});