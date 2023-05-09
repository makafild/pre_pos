<?php

use Core\Packages\comment\src\controllers\CommentController;

$prefix = config('core.prefix') . '/comments';

Route::group(['prefix' => $prefix], function () {
    Route::group(['middleware' => ['jwt']], function () {

        Route::get('/sign', [CommentController::class, 'sign'])->name('comment.sign');
        Route::get('/{type}', [CommentController::class, 'list'])->name('comment.list');
        Route::post('/confirm', [CommentController::class, 'confirm'])->name('comment.confirm');
        Route::post('/{comment_id}/replay', [CommentController::class, 'replay'])->name('comment.replay');
        Route::get('/{id}', [CommentController::class, 'list'])->name('comment.show');
        Route::delete('/', [CommentController::class, 'destroy'])->name('comment.destroy');
    });
});
