<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\Http\Controllers\ChatController;
use Modules\Chat\Http\Controllers\ConversationController;
use Modules\Chat\Http\Controllers\MessageController;
use Modules\Role\Enums\Permission;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function (): void {
    Route::resource('chat', ChatController::class)->names('chat');
});

/**
 * ******************************************
 * Authorized Route for Customers only
 * ******************************************
 */
Route::group(['middleware' => ['can:'.Permission::CUSTOMER, 'auth:sanctum', 'email.verified']], function (): void {
    Route::apiResource('conversations', ConversationController::class, [
        'only' => ['index', 'store'],
    ]);
    Route::get('conversations/{conversation_id}', [ConversationController::class, 'show']);
    Route::get('messages/conversations/{conversation_id}', [MessageController::class, 'index']);
    Route::post('messages/conversations/{conversation_id}', [MessageController::class, 'store']);
    Route::post('messages/seen/{conversation_id}', [MessageController::class, 'seen']);
});
