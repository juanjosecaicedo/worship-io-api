<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List user notifications
     * 
     * Returns a paginated list of notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at');

        // Filtrar por leídas/no leídas
        if ($request->boolean('unread')) {
            $query->unread();
        }

        $notifications = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'unread_count' => Notification::forUser($request->user()->id)
                    ->unread()
                    ->count(),
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json([
            'message' => __('notifications.marked_read'),
            'data' => new NotificationResource($notification),
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => __('notifications.all_marked_read'),
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->delete();

        return response()->json([
            'message' => __('notifications.deleted_success'),
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function destroyRead(Request $request): JsonResponse
    {
        Notification::forUser($request->user()->id)
            ->read()
            ->delete();

        return response()->json([
            'message' => __('notifications.read_deleted'),
        ]);
    }
}
