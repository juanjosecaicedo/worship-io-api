<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Event\EventAttendeeController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Event\EventRoleController;
use App\Http\Controllers\GlobalSong\GlobalSongController;
use App\Http\Controllers\GlobalSong\GlobalSongSectionController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Group\GroupMemberController;
use App\Http\Controllers\GroupSong\GroupSongController;
use App\Http\Controllers\GroupSong\GroupSongSectionController;
use App\Http\Controllers\GroupSong\SongNoteController;
use App\Http\Controllers\GroupSong\UserSongKeyController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Notification\ReminderController;
use App\Http\Controllers\Setlist\SetlistController;
use App\Http\Controllers\Setlist\SetlistSongController;
use App\Http\Controllers\Setlist\SetlistVocalistController;
use App\Http\Controllers\Subscription\InvoiceController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Subscription\WebhookController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;

// ─── Autenticación (pública) ──────────────────────────────
Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// ─── Webhooks (sin auth) ──────────────────────────────────
Route::prefix('v1/webhooks')->group(function () {
    Route::post('stripe', [WebhookController::class, 'stripe']);
    Route::post('mercadopago', [WebhookController::class, 'mercadopago']);
    Route::post('paypal', [WebhookController::class, 'paypal']);
});

// ─── Rutas protegidas con Sanctum ────────────────────────
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // ─── Auth ───────────────────────────────────
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // ─── User ───────────────────────────────────
    Route::patch('user/profile', [UserController::class, 'update']);
    Route::patch('user/vocal-profile', [UserController::class, 'updateVocalProfile']);
    Route::delete('user', [UserController::class, 'destroy']);

    // ─── Grupos ───────────────────────────────────
    Route::apiResource('groups', GroupController::class);

    // ─── Miembros del grupo ───────────────────────
    Route::prefix('groups/{group}/members')->group(function () {
        Route::get('/', [GroupMemberController::class, 'index']);
        Route::post('/', [GroupMemberController::class, 'store']);
        Route::patch('/{member}', [GroupMemberController::class, 'update']);
        Route::delete('/{member}', [GroupMemberController::class, 'destroy']);
    });

    // ─── Canciones Globales ───────────────────────────────
    Route::prefix('songs/global')->group(function () {
        Route::get('/', [GlobalSongController::class, 'index']);
        Route::post('/', [GlobalSongController::class, 'store']);
        Route::get('/{globalSong}', [GlobalSongController::class, 'show']);
        Route::patch('/{globalSong}', [GlobalSongController::class, 'update']);
        Route::delete('/{globalSong}', [GlobalSongController::class, 'destroy']);

        // Secciones
        Route::prefix('/{globalSong}/sections')->group(function () {
            Route::post('/', [GlobalSongSectionController::class, 'store']);
            Route::post('/reorder', [GlobalSongSectionController::class, 'reorder']);
            Route::patch('/{section}', [GlobalSongSectionController::class, 'update']);
            Route::delete('/{section}', [GlobalSongSectionController::class, 'destroy']);
        });
    });

    // ─── Canciones del Grupo ──────────────────────────────
    Route::prefix('groups/{group}/songs')->group(function () {
        Route::get('/', [GroupSongController::class, 'index']);
        Route::post('/', [GroupSongController::class, 'store']);

        // Fork de canción global
        Route::post('/fork/{globalSong}', [GroupSongController::class, 'fork']);

        Route::get('/{groupSong}', [GroupSongController::class, 'show']);
        Route::patch('/{groupSong}', [GroupSongController::class, 'update']);
        Route::delete('/{groupSong}', [GroupSongController::class, 'destroy']);

        // Secciones
        Route::prefix('/{groupSong}/sections')->group(function () {
            Route::post('/', [GroupSongSectionController::class, 'store']);
            Route::post('/reorder', [GroupSongSectionController::class, 'reorder']);
            Route::patch('/{section}', [GroupSongSectionController::class, 'update']);
            Route::delete('/{section}', [GroupSongSectionController::class, 'destroy']);
        });

        // Notas personales
        Route::prefix('/{groupSong}/notes')->group(function () {
            Route::get('/', [SongNoteController::class, 'index']);
            Route::post('/', [SongNoteController::class, 'store']);
            Route::patch('/{note}', [SongNoteController::class, 'update']);
            Route::delete('/{note}', [SongNoteController::class, 'destroy']);
        });

        // Tono preferido por vocalista
        Route::prefix('/{groupSong}/my-key')->group(function () {
            Route::get('/', [UserSongKeyController::class, 'show']);
            Route::post('/', [UserSongKeyController::class, 'upsert']);
            Route::delete('/', [UserSongKeyController::class, 'destroy']);
        });
    });

    // ─── Eventos ──────────────────────────────────────────
    Route::prefix('groups/{group}/events')->group(function () {
        Route::get('/', [EventController::class, 'index']);
        Route::post('/', [EventController::class, 'store']);
        Route::get('/{event}', [EventController::class, 'show']);
        Route::patch('/{event}', [EventController::class, 'update']);
        Route::delete('/{event}', [EventController::class, 'destroy']);

        // Roles del evento
        Route::prefix('/{event}/roles')->group(function () {
            Route::post('/', [EventRoleController::class, 'store']);
            Route::patch('/{role}', [EventRoleController::class, 'update']);
            Route::delete('/{role}', [EventRoleController::class, 'destroy']);
        });

        // Asistencia
        Route::prefix('/{event}/attendees')->group(function () {
            Route::get('/', [EventAttendeeController::class, 'index']);
            Route::post('/respond', [EventAttendeeController::class, 'respond']);
            Route::post('/mark', [EventAttendeeController::class, 'markAttendance']);
        });
    });

    // ─── Setlists ─────────────────────────────────────────
    Route::prefix('groups/{group}/events/{event}/setlists')->group(function () {
        Route::get('/', [SetlistController::class, 'index']);
        Route::post('/', [SetlistController::class, 'store']);
        Route::get('/{setlist}', [SetlistController::class, 'show']);
        Route::patch('/{setlist}', [SetlistController::class, 'update']);
        Route::delete('/{setlist}', [SetlistController::class, 'destroy']);

        // Canciones del setlist
        Route::prefix('/{setlist}/songs')->group(function () {
            Route::post('/', [SetlistSongController::class, 'store']);
            Route::post('/reorder', [SetlistSongController::class, 'reorder']);
            Route::patch('/{setlistSong}', [SetlistSongController::class, 'update']);
            Route::delete('/{setlistSong}', [SetlistSongController::class, 'destroy']);

            // Vocalistas por canción del setlist
            Route::prefix('/{setlistSong}/vocalists')->group(function () {
                Route::post('/', [SetlistVocalistController::class, 'store']);
                Route::patch('/{vocalist}', [SetlistVocalistController::class, 'update']);
                Route::delete('/{vocalist}', [SetlistVocalistController::class, 'destroy']);
            });
        });
    });

    // Subscripción del usuario
    Route::prefix('subscription')->group(function () {
        Route::get('/', [SubscriptionController::class, 'show']);
        Route::post('/', [SubscriptionController::class, 'store']);
        Route::patch('/change-plan', [SubscriptionController::class, 'changePlan']);
        Route::delete('/cancel', [SubscriptionController::class, 'cancel']);
    });

    // Facturas
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
    });

    // ─── Notificaciones del usuario ───────────────────────
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/read', [NotificationController::class, 'destroyRead']);
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'destroy']);
    });

    // ─── Recordatorios por evento ─────────────────────────
    Route::prefix('groups/{group}/events/{event}/reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index']);
        Route::post('/', [ReminderController::class, 'store']);
        Route::delete('/{reminder}', [ReminderController::class, 'destroy']);
    });

    // ─── Preferencias del usuario ─────────────────────────
    Route::prefix('user/preferences')->group(function () {
        Route::get('/',           [UserPreferenceController::class, 'index']);
        Route::get('/defaults',   [UserPreferenceController::class, 'defaults']);
        Route::patch('/',         [UserPreferenceController::class, 'update']);
        Route::patch('/bulk',     [UserPreferenceController::class, 'bulkUpdate']);
        Route::delete('/reset-all', [UserPreferenceController::class, 'resetAll']);
        Route::delete('/{key}',   [UserPreferenceController::class, 'reset']);
    });
});
