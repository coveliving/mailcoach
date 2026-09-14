<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\Api\SyncCommunityPlusMembersController;
use Illuminate\Support\Facades\Route;

// routes/api.php is already wrapped in the "api" middleware group by bootstrap/app.php,
// so only auth:sanctum needs adding here (mirrors Mailcoach's own API auth).
Route::post('community-plus-members/sync', SyncCommunityPlusMembersController::class)
    ->middleware('auth:sanctum');
