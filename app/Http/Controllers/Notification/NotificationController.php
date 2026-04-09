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
     * 
     * @param Request $request
     * @return \Illuminate\Http\Resources\JsonApi\AnonymousResourceCollection
     */
    public function index(Request $request): \Illuminate\Http\Resources\JsonApi\AnonymousResourceCollection
    {
        $query = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at');

        // Filtrar por leídas/no leídas
        if ($request->boolean('unread')) {
            $query->unread();
        }

        $notifications = $query->paginate($request->integer('per_page', 20));

        $unreadCount = Notification::forUser($request->user()->id)
            ->unread()
            ->count();

        return NotificationResource::collection($notifications)
            ->additional(['meta' => [
                'unread_count' => $unreadCount,
            ]]);
    }

    /**
     * Mark notification as read
     * 
     * @param Request $request
     * @param Notification $notification
     * @return NotificationResource
     */
    public function markAsRead(Request $request, Notification $notification): NotificationResource
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->markAsRead();

        return (new NotificationResource($notification))
            ->additional(['message' => __('notifications.marked_read')]);
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
