<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of user's notifications.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications();

        // Filter by read status
        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error,booking,payment,system',
            'user_id' => 'sometimes|exists:users,id',
            'data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $notification = Notification::create([
            'user_id' => $request->user_id ?? Auth::id(),
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'data' => $request->data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully',
            'data' => $notification
        ], 201);
    }

    /**
     * Display the specified notification.
     */
    public function show($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        // Mark as read when viewed
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['read_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $count = Auth::user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read"
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Delete all read notifications.
     */
    public function clearRead()
    {
        $count = Auth::user()->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$count} read notifications"
        ]);
    }

    /**
     * Get notification statistics.
     */
    public function stats()
    {
        $user = Auth::user();

        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->notifications()->whereNull('read_at')->count(),
            'read' => $user->notifications()->whereNotNull('read_at')->count(),
            'by_type' => $user->notifications()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'recent_count' => $user->notifications()
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        $count = Auth::user()->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Send notification to user (admin only).
     */
    public function sendToUser(Request $request, $userId)
    {
        // Check if user is admin
        if (!Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error,booking,payment,system',
            'data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'data' => $request->data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully',
            'data' => $notification
        ], 201);
    }

    /**
     * Broadcast notification to all users (admin only).
     */
    public function broadcast(Request $request)
    {
        // Check if user is admin
        if (!Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error,booking,payment,system',
            'data' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $userIds = \App\Models\User::where('is_admin', false)->pluck('id');
        $notifications = [];

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'title' => $request->title,
                'message' => $request->message,
                'type' => $request->type,
                'data' => json_encode($request->data ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Notification::insert($notifications);

        return response()->json([
            'success' => true,
            'message' => 'Notification broadcasted to all users',
            'recipient_count' => count($notifications)
        ]);
    }

    /**
     * Send booking-related notifications.
     */
    public static function sendBookingNotification($userId, $type, $bookingData)
    {
        $titles = [
            'booking_created' => 'Booking Confirmed',
            'booking_updated' => 'Booking Updated',
            'booking_cancelled' => 'Booking Cancelled',
            'charging_started' => 'Charging Started',
            'charging_completed' => 'Charging Completed',
            'payment_processed' => 'Payment Processed',
        ];

        $messages = [
            'booking_created' => 'Your charging session has been booked successfully.',
            'booking_updated' => 'Your booking details have been updated.',
            'booking_cancelled' => 'Your booking has been cancelled.',
            'charging_started' => 'Your charging session has started.',
            'charging_completed' => 'Your charging session has completed.',
            'payment_processed' => 'Payment for your charging session has been processed.',
        ];

        Notification::create([
            'user_id' => $userId,
            'title' => $titles[$type] ?? 'Booking Update',
            'message' => $messages[$type] ?? 'Your booking has been updated.',
            'type' => 'booking',
            'data' => $bookingData,
        ]);
    }
}
