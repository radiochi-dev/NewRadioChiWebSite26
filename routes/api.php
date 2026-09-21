<?php

use App\Http\Controllers\Automation\AutomationPublicHomePayloadController;
use App\Http\Controllers\Automation\AutomationStatusController;
use App\Http\Controllers\Automation\N8nResultWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('internal/automation')
    ->middleware(['automation.signature', 'throttle:60,1'])
    ->group(function () {
        Route::get('public-home/{locale}', AutomationPublicHomePayloadController::class);
        Route::get('logs/{automationLog}', AutomationStatusController::class);
        Route::post('n8n/results', N8nResultWebhookController::class);
    });
