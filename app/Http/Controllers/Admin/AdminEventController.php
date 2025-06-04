<?php

namespace App\Http\Controllers\Admin;

use App\Models\VitalAid\Event;
use App\Models\VitalAid\EventParticipant;
use App\Models\VitalAid\EventReaction;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminEventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('eventManager');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_time', '<=', $request->date_to);
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $events = $query->paginate(20);

        return view('admin.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load(['eventManager', 'eventParticipants.user', 'eventReactions.user']);

        $eventStats = $this->getEventStats($event);
        $participants = $event->eventParticipants()->with('user')->paginate(20);
        $reactions = $event->eventReactions()->with('user')->latest()->take(10)->get();

        return view('admin.events.show', compact('event', 'eventStats', 'participants', 'reactions'));
    }

    public function edit(Event $event)
    {
        $eventManagers = User::where('role', 'community')->get();
        return view('admin.events.edit', compact('event', 'eventManagers'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'event_manager' => 'required|exists:users,_id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'status' => 'required|in:draft,active,cancelled,completed',
            'category' => 'required|string|max:100',
            'capacity' => 'nullable|integer|min:1',
            'contact_info' => 'nullable|string',
            'requires_verification' => 'boolean',
            'provides_certificate' => 'boolean',
            'banner_url' => 'nullable|url'
        ]);

        $event->update($request->all());

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        // Check if event has participants
        if ($event->eventParticipants()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete event with participants.');
        }

        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    public function updateStatus(Request $request, Event $event)
    {
        $request->validate([
            'status' => 'required|in:draft,active,cancelled,completed'
        ]);

        $event->update(['status' => $request->status]);

        return redirect()->back()
            ->with('success', 'Event status updated successfully.');
    }

    public function participants(Event $event, Request $request)
    {
        $query = $event->eventParticipants()->with('user');

        // Filter by participant status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search participants
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $participants = $query->paginate(20);

        return view('admin.events.participants', compact('event', 'participants'));
    }

    public function removeParticipant(Event $event, EventParticipant $participant)
    {
        $participant->delete();

        return redirect()->back()
            ->with('success', 'Participant removed successfully.');
    }

    private function getEventStats(Event $event)
    {
        $totalParticipants = $event->eventParticipants()->count();
        $confirmedParticipants = $event->eventParticipants()->where('status', 'confirmed')->count();
        $pendingParticipants = $event->eventParticipants()->where('status', 'pending')->count();

        $totalReactions = $event->eventReactions()->count();
        $likes = EventReaction::countLikes($event->_id);
        $dislikes = EventReaction::countDislikes($event->_id);

        return [
            'participants' => [
                'total' => $totalParticipants,
                'confirmed' => $confirmedParticipants,
                'pending' => $pendingParticipants,
                'attendance_rate' => $totalParticipants > 0 ? round(($confirmedParticipants / $totalParticipants) * 100, 1) : 0
            ],
            'reactions' => [
                'total' => $totalReactions,
                'likes' => $likes,
                'dislikes' => $dislikes,
                'like_ratio' => $totalReactions > 0 ? round(($likes / $totalReactions) * 100, 1) : 0
            ],
            'capacity_utilization' => $event->capacity ? round(($totalParticipants / $event->capacity) * 100, 1) : null
        ];
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,cancel,delete',
            'event_ids' => 'required|array',
            'event_ids.*' => 'exists:events,_id'
        ]);

        $events = Event::whereIn('_id', $request->event_ids);

        switch ($request->action) {
            case 'activate':
                $events->update(['status' => 'active']);
                $message = 'Events activated successfully.';
                break;

            case 'deactivate':
                $events->update(['status' => 'draft']);
                $message = 'Events deactivated successfully.';
                break;

            case 'cancel':
                $events->update(['status' => 'cancelled']);
                $message = 'Events cancelled successfully.';
                break;

            case 'delete':
                $events->delete();
                $message = 'Events deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
