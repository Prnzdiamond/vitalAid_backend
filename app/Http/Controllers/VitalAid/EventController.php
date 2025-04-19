<?php

namespace App\Http\Controllers\VitalAid;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\VitalAid\Event;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\VitalAid\EventParticipant;

class EventController extends Controller
{
    // ✅ Apply authentication middleware to all methods except `index()` and `show()`

    // ✅ Fetch all events (Public)
    public function index()
    {
        return response()->json([
            'events' => Event::all()
        ]);
    }

    // ✅ Show a single event (Public)
    public function show($eventId)
    {

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        return response()->json([
            'event' => $event
        ]);
    }

    // ✅ Create an event (Authenticated users only)
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'event_manager' => $user->id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'upcoming',
        ]);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event
        ], 201);
    }

    // ✅ User joins an event (Authenticated users only)
    public function joinEvent(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if user already joined
        $existingParticipant = EventParticipant::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->first();

        if ($existingParticipant) {
            return response()->json(['message' => 'You have already joined this event'], 409);
        }

        EventParticipant::create([
            'event_id' => $eventId,
            'user_id' => $user->id,
            'status' => 'joined',
        ]);

        return response()->json(['message' => 'Successfully joined event']);
    }

    // ✅ Get event participants (Authenticated users only)
    public function getEventParticipants(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $participants = EventParticipant::where('event_id', $eventId)->get();

        return response()->json([
            'event' => $event,
            'participants' => $participants
        ]);
    }

    // ✅ Fetch user's joined events (Authenticated users only)
    public function userEvents(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Fetch the events the user has joined
        $joinedEventIds = EventParticipant::where('user_id', $user->id)->pluck('event_id');
        $events = Event::whereIn('_id', $joinedEventIds)->get();

        return response()->json([
            'joined_events' => $events
        ]);
    }

    public function createdEvents(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Fetch the events created by the user
        $events = Event::where('event_manager', $user->id)->get();

        return response()->json([
            'created_events' => $events
        ]);
    }
    public function updateEvent(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if the user is the event manager
        if ($event->event_manager !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
        ]);

        $event->update($request->all());

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }
    public function deleteEvent(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if the user is the event manager
        if ($event->event_manager !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }
    public function leaveEvent(Request $request, $eventId)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Check if user is a participant
        $participant = EventParticipant::where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'You are not a participant of this event'], 409);
        }

        $participant->delete();

        return response()->json(['message' => 'Successfully left the event']);
    }


}
