<?php
use Core\Packages\role\src\controllers\RolePackageController;


  Route::group(['middleware' => ['jwt']], function () {
  $prefix = config('core.prefix') . '/roles';
Route::group(['prefix' => $prefix ], function () {
    // Route::post('/', [RolePackageController::class, 'store'])->name('role.store');
    // Route::get('/', [RolePackageController::class, 'list'])->name('role.list')->name('role.list');
    // Route::get('/system_routes', [RolePackageController::class, 'system_routes'])->name('role.system.routes');
    // Route::get('/permission_routes', [RolePackageController::class, 'permission_routes'])->name('role.permission.routes');
    // Route::get('/{id}', [RolePackageController::class, 'show'])->name('role.show')->name('role.show');
    // Route::post('/{id}', [RolePackageController::class, 'update'])->name('role.update');
    // Route::post('/user/{id}', [RolePackageController::class, 'assign_roles_to_user'])->name('role.user.store');
    // Route::get('/user/{id}', [RolePackageController::class, 'user_roles_show'])->name('role.user.show');
});
});
