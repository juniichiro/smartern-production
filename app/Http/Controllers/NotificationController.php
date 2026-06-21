<?php

namespace App\Http\Controllers;

use App\Events\RealTimeNotificationSent;
use App\Mail\GenericExternalNotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    /**
     * Updated Send Method: Now accepts an optional targetUrl
     */
    public static function send($recipientId, $type, $category, $message, $targetUrl = null, $isExternal = false, ?array $extraData = null)
    {
        $notification = Notification::create([
            'recipient_id' => $recipientId,
            'sender_id' => Auth::id(),
            'type' => $type,
            'category' => $category,
            'message' => $message,
            'target_url' => $targetUrl, // Save the redirect link
            'is_read' => false,
            'is_external' => $isExternal,
        ]);

        // Dispatch Real-Time Event
        try {
            broadcast(new RealTimeNotificationSent($notification));
        } catch (\Exception $e) {
            // Suppress broadcast errors if Reverb is offline
        }

        // Dispatch External Email Notification if requested
        if ($isExternal) {
            try {
                $recipient = User::find($recipientId);
                if ($recipient && $recipient->email) {
                    Mail::to($recipient->email)->send(new GenericExternalNotificationMail($notification, $extraData));
                }
            } catch (\Exception $e) {
                Log::error("Failed to send external notification email to user {$recipientId}: ".$e->getMessage());
            }
        }

        return $notification;
    }

    public function resolve(Notification $notification)
    {
        if ($notification->recipient_id !== Auth::id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => $notification->target_url ?? route('intern.progress'),
            ]);
        }

        // Fallback for non-AJAX requests
        return redirect($notification->target_url ?? route('intern.progress'));
    }

    /**
     * Mark a single notification as read (without redirecting)
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->recipient_id !== Auth::id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification cleared.');
    }

    public function markAllAsRead()
    {
        Notification::where('recipient_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications have been cleared.');
    }

    public function index()
    {
        $notifications = Notification::where('recipient_id', Auth::id())
            ->with('sender')
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }
}
