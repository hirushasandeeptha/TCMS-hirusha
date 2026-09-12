<?php

namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Notification\Models\Notification;

final class NotificationController extends Controller
{
    private function resolveNotification($id): Notification
    {
        return Notification::findOrFail($id);
    }

    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->with('student')
            ->when($request->type, fn ($q, $type) =>
                $q->where('type', $type)
            )
            ->when($request->channel, fn ($q, $channel) =>
                $q->where('channel', $channel)
            )
            ->when($request->status, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->when($request->boolean('unread_only'), fn ($q) =>
                $q->whereNull('read_at')
            )
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        if ($request->expectsJson()) {
            return response()->json($notifications);
        }

        return Inertia::render('Notifications/Index', ['notifications' => $notifications]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['nullable', 'exists:students,id'],
            'type'       => ['required', 'string', 'max:100'],
            'title'      => ['required', 'string', 'max:255'],
            'body'       => ['nullable', 'string', 'max:2000'],
            'channel'    => ['required', 'in:sms,whatsapp,email,in_app'],
            'meta'       => ['nullable', 'array'],
        ]);

        $notification = Notification::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message'      => 'Notification created successfully.',
                'notification' => $notification,
            ], 201);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification created successfully.');
    }

    public function show(Notification $notification)
    {
        $notification = $this->resolveNotification($notification->id);
        $notification->load('student');

        if (request()->expectsJson()) {
            return response()->json(['notification' => $notification]);
        }

        return Inertia::render('Notifications/Show', ['notification' => $notification]);
    }

    public function update(Request $request, Notification $notification)
    {
        $notification = $this->resolveNotification($notification->id);

        $validated = $request->validate([
            'title'    => ['sometimes', 'string', 'max:255'],
            'body'     => ['nullable', 'string', 'max:2000'],
            'status'   => ['sometimes', 'in:draft,sent,failed,read'],
            'sent_at'  => ['nullable', 'date'],
            'meta'     => ['nullable', 'array'],
        ]);

        $notification->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message'      => 'Notification updated successfully.',
                'notification' => $notification,
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification updated successfully.');
    }

    public function markAsRead(Notification $notification)
    {
        $notification = $this->resolveNotification($notification->id);
        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Notification marked as read.']);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function destroy(Notification $notification)
    {
        $notification = $this->resolveNotification($notification->id);
        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Notification deleted successfully.',
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }
}
