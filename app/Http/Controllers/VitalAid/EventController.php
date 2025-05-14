<?php

namespace App\Http\Controllers\VitalAid;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\VitalAid\Event;
use App\Services\EventService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\VitalAid\EventParticipant;
use Illuminate\Support\Facades\Validator;
use App\Notifications\GeneralNotification;
use App\Http\Resources\VitalAid\EventResource;
use App\Http\Resources\VitalAid\EventParticipantResource;

class EventController extends Controller
{
    // Common response formats
    private function response($success = true, $data = [], $message = '', $code = 200)
    {
        return response()->json(
            array_filter([
                'success' => $success,
                'message' => $message ?: null,
                'data' => !empty($data) ? $data : null,
                'errors' => $success ? null : ($data ?: null)
            ]),
            $code
        );
    }

    // Auth check helper
    private function checkAuth(Request $request)
    {
        return $request->user() ?: $this->response(false, [], 'Unauthorized.', 401);
    }

    private function isEventCompleted($event)
    {
        return $event->status === 'completed' || now() > $event->end_time || now() > $event->start_time;
    }

    // Check if user is event manager
    private function isEventManager($event, $userId)
    {
        if ($event->event_manager !== $userId) {
            return false;
        }
        return true;
    }

    // Generic event query builder with filters
    private function buildEventQuery(Request $request)
    {
        $query = Event::query();

        // Search filter
        if ($search = $request->query('search') ?: $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($community = $request->query('communityID')) {
            Log::info($community);
            $query->where('event_manager', $community);
        }

        // Category filter
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        // Status filter
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        } elseif (!$request->has('status')) {
            $query->where('status', '!=', 'deleted');
        }

        // Time frame filter
        if ($timeFrame = $request->query('time_frame')) {
            $now = now();
            if ($timeFrame === 'past') {
                $query->where(function ($q) use ($now) {
                    $q->where('end_time', '<', $now)
                        ->orWhere('status', 'completed');
                });
            } elseif ($timeFrame === 'upcoming') {
                $query->where(function ($q) use ($now) {
                    $q->where('end_time', '>=', $now)
                        ->where('status', '!=', 'completed');
                });
            }
        }

        // Sort
        $sortBy = $request->query('sort_by', 'start_time');
        $sortDirection = $request->query('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        return $query;
    }

    // ✅ List and search events (Public)
    public function index(Request $request)
    {
        try {
            $query = $this->buildEventQuery($request);
            $perPage = $request->query('per_page', 12);
            $events = $query->paginate($perPage);

            return $this->response(true, [
                'events' => EventResource::collection($events),
                'pagination' => [
                    'total' => $events->total(),
                    'per_page' => $events->perPage(),
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch events: ' . $e->getMessage());
            return $this->response(false, [], 'Failed to fetch events.');
        }
    }

    // Alias for index with different parameter name
    public function search(Request $request)
    {
        return $this->index($request);
    }

    // ✅ Show a single event (Public)
    public function show($eventId)
    {
        try {
            return $this->response(true, ['event' => new EventResource(Event::findOrFail($eventId))]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->response(false, [], 'Event not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch event: ' . $e->getMessage());
            return $this->response(false, [], 'Failed to fetch event.');
        }
    }

    // Handle event image upload
    private function handleEventImage($request, $event = null)
    {
        if (!$request->hasFile('banner_image')) {
            return null;
        }

        // Delete old banner if exists
        if ($event && $event->banner_url) {
            $oldPath = str_replace(Storage::url(''), 'public/', $event->banner_url);
            if (Storage::exists($oldPath)) {
                Storage::delete($oldPath);
            }
        }

        $banner = $request->file('banner_image');
        $bannerName = time() . '_' . $banner->getClientOriginalName();
        $banner->storeAs('event_banners', $bannerName, 'public');
        return asset(Storage::url('event_banners/' . $bannerName));
    }

    // Create a new event
    public function store(Request $request)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'category' => 'sometimes|string|max:50',
            'capacity' => 'sometimes|integer|min:1',
            'contact_info' => 'sometimes|string|max:255',
            'requires_verification' => 'sometimes|boolean',
            'provides_certificate' => 'sometimes|boolean',
            'banner_image' => 'sometimes|image|mimes:jpeg,png|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->response(false, $validator->errors(), 'Validation error.', 422);
        }

        try {
            $eventData = $request->except(['banner_image']) + [
                'event_manager' => $user->id,
                'status' => 'upcoming',
                'category' => $request->category ?? 'community',
            ];

            $bannerUrl = $this->handleEventImage($request);
            if ($bannerUrl) {
                $eventData['banner_url'] = $bannerUrl;
            }

            $event = Event::create($eventData);

            // Notify community members
            if ($user->role === 'community') {
                $this->notifyCommunityMembersAboutEvent($user->id, $event);
            }

            return $this->response(true, ['event' => new EventResource($event)], 'Event created successfully.', 201);
        } catch (\Exception $e) {
            Log::error('Failed to create event: ' . $e->getMessage());
            return $this->response(false, [], 'Failed to create event.');
        }
    }

    // Update an event
    public function updateEvent(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            $event = Event::findOrFail($eventId);

            if (!$this->isEventManager($event, $user->id)) {
                return $this->response(false, [], 'Unauthorized.', 403);
            }

            if ($this->isEventCompleted($event)) {
                return $this->response(false, [], 'Cannot edit a completed event.', 403);
            }

            $rules = [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'location' => 'sometimes|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time',
                'category' => 'sometimes|string|max:50',
                'capacity' => 'sometimes|integer|min:1',
                'contact_info' => 'sometimes|string|max:255',
                'requires_verification' => 'sometimes|boolean',
                'provides_certificate' => 'sometimes|boolean',
                'banner_image' => 'sometimes|image|mimes:jpeg,png|max:2048',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->response(false, $validator->errors(), 'Validation error.', 422);
            }

            $eventData = $request->except(['banner_image']);

            $bannerUrl = $this->handleEventImage($request, $event);
            if ($bannerUrl) {
                $eventData['banner_url'] = $bannerUrl;
            }

            // Check if significant changes were made to notify participants
            $significantChanges = false;
            $changesDescription = [];

            if (isset($eventData['start_time']) && $event->start_time != $eventData['start_time']) {
                $significantChanges = true;
                $changesDescription[] = 'date/time';
            }
            if (isset($eventData['location']) && $event->location != $eventData['location']) {
                $significantChanges = true;
                $changesDescription[] = 'location';
            }
            if (isset($eventData['title']) && $event->title != $eventData['title']) {
                $significantChanges = true;
                $changesDescription[] = 'title';
            }

            $event->update($eventData);

            // If significant changes, notify participants
            if ($significantChanges) {
                $participants = EventParticipant::where('event_id', $eventId)
                    ->where('status', 'joined')
                    ->get();

                foreach ($participants as $participant) {
                    $user = User::find($participant->user_id);
                    if ($user && $user->id !== $event->event_manager) {
                        $user->notify(new GeneralNotification([
                            'title' => 'Event Update: ' . $event->title,
                            'body' => 'An event you registered for has been updated. Changes to: ' . implode(', ', $changesDescription),
                            'type' => 'event_updated',
                            'extra' => [
                                'event_id' => $eventId,
                                'route' => "/events/$eventId",
                                'changes' => $changesDescription
                            ]
                        ]));
                    }
                }
            }

            return $this->response(true, ['event' => new EventResource($event)], 'Event updated successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->response(false, [], 'Event not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to update event ' . $eventId . ': ' . $e->getMessage());
            return $this->response(false, [], 'Failed to update event.');
        }
    }

    // Generic event action handler
    private function handleEventAction($eventId, $userId, $action)
    {
        try {
            $event = Event::findOrFail($eventId);

            // Common action validations
            if ($this->isEventCompleted($event) && $action != 'delete') {
                return $this->response(false, [], "Cannot $action a completed event.", 403);
            }

            switch ($action) {
                case 'join':
                    if (EventParticipant::where('event_id', $eventId)->where('user_id', $userId)->exists()) {
                        return $this->response(false, [], 'You have already joined this event.', 409);
                    }

                    if ($event->capacity && EventParticipant::where('event_id', $eventId)->count() >= $event->capacity) {
                        return $this->response(false, [], 'Event has reached maximum capacity.', 409);
                    }

                    $participant = EventParticipant::create([
                        'event_id' => $eventId,
                        'user_id' => $userId,
                        'status' => $event->requires_verification ? 'pending' : 'joined',
                    ]);

                    // Notify event manager about new participant
                    if ($event->requires_verification) {
                        $eventManager = User::find($event->event_manager);
                        if ($eventManager) {
                            $user = User::find($userId);
                            $eventManager->notify(new GeneralNotification([
                                'title' => 'New Event Participant Request',
                                'body' => ($user ? $user->name : 'A user') . ' has requested to join your event: ' . $event->title,
                                'type' => 'event_join_request',
                                'extra' => [
                                    'event_id' => $eventId,
                                    'participant_id' => $participant->id,
                                    'route' => "/events/$eventId/participants"
                                ]
                            ]));
                        }
                    }

                    $message = $event->requires_verification ?
                        'Successfully requested to join event. Awaiting organizer approval.' :
                        'Successfully joined event.';
                    return $this->response(true, [], $message);

                case 'leave':
                    $participant = EventParticipant::where('event_id', $eventId)
                        ->where('user_id', $userId)
                        ->first();

                    if (!$participant) {
                        return $this->response(false, [], 'You are not a participant of this event.', 409);
                    }

                    // Notify event manager that a participant left
                    $eventManager = User::find($event->event_manager);
                    if ($eventManager && $eventManager->id !== $userId) {
                        $user = User::find($userId);
                        $eventManager->notify(new GeneralNotification([
                            'title' => 'Participant Left Event',
                            'body' => ($user ? $user->name : 'A participant') . ' has left your event: ' . $event->title,
                            'type' => 'event_participant_left',
                            'extra' => [
                                'event_id' => $eventId,
                                'route' => "/events/$eventId"
                            ]
                        ]));
                    }

                    $participant->delete();
                    return $this->response(true, [], 'Successfully left the event.');

                case 'delete':
                    if (!$this->isEventManager($event, $userId)) {
                        return $this->response(false, [], 'Unauthorized. Only event managers can delete their events.', 403);
                    }

                    // Get all participants before deleting them
                    $participants = EventParticipant::where('event_id', $eventId)->get();

                    // Notify all participants about event cancellation
                    foreach ($participants as $participant) {
                        $user = User::find($participant->user_id);
                        if ($user && $user->id !== $userId) {
                            $user->notify(new GeneralNotification([
                                'title' => 'Event Cancelled: ' . $event->title,
                                'body' => 'An event you registered for has been cancelled by the organizer.',
                                'type' => 'event_cancelled',
                                'extra' => [
                                    'event_id' => $eventId,
                                    'event_title' => $event->title,
                                    'was_scheduled_for' => $event->start_time,
                                    'route' => '/events'
                                ]
                            ]));
                        }
                    }

                    // Now delete participants and event
                    EventParticipant::where('event_id', $eventId)->delete();
                    $event->delete();

                    return $this->response(true, [], 'Event deleted successfully and all participants notified.');

                case 'complete':
                    if (!$this->isEventManager($event, $userId)) {
                        return $this->response(false, [], 'Unauthorized. Only event managers can complete their events.', 403);
                    }

                    if ($event->status === 'completed') {
                        return $this->response(false, [], 'Event is already marked as completed.', 409);
                    }

                    $service = app()->make(EventService::class);
                    $success = $service::completeEvent($eventId);

                    return $success ?
                        $this->response(true, [], 'Event marked as completed and participants notified for feedback.') :
                        $this->response(false, [], 'Failed to mark event as completed.');
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->response(false, [], 'Event not found.', 404);
        } catch (\Exception $e) {
            Log::error("Failed to $action event $eventId: " . $e->getMessage());
            return $this->response(false, [], "Failed to $action event.");
        }
    }

    // ✅ User joins an event
    public function joinEvent(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }
        return $this->handleEventAction($eventId, $user->id, 'join');
    }

    // Leave event
    public function leaveEvent(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }
        return $this->handleEventAction($eventId, $user->id, 'leave');
    }

    // Delete event
    public function deleteEvent(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }
        return $this->handleEventAction($eventId, $user->id, 'delete');
    }

    // Mark event as completed
    public function completeEvent(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }
        return $this->handleEventAction($eventId, $user->id, 'complete');
    }

    // Get event participants
    public function getEventParticipants(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            $event = Event::findOrFail($eventId);
            $participants = EventParticipant::where('event_id', $eventId)->get();

            return $this->response(true, [
                'event' => new EventResource($event),
                'participants' => EventParticipantResource::collection($participants)
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->response(false, [], 'Event not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch participants for event ' . $eventId . ': ' . $e->getMessage());
            return $this->response(false, [], 'Failed to fetch event participants.');
        }
    }

    // ✅ Fetch user's events - joined and created
    public function userEvents(Request $request)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            $type = $request->query('type', 'joined');

            if ($type === 'joined') {
                $joinedEventIds = EventParticipant::where('user_id', $user->id)->pluck('event_id');
                $events = Event::whereIn('_id', $joinedEventIds)->get();
                return $this->response(true, ['joined_events' => EventResource::collection($events)]);
            } else {
                $events = Event::where('event_manager', $user->id)->get();
                return $this->response(true, ['created_events' => EventResource::collection($events)]);
            }
        } catch (\Exception $e) {
            $action = $request->query('type', 'joined') === 'joined' ? 'joined' : 'created';
            Log::error("Failed to fetch {$action} events for user {$user->id}: " . $e->getMessage());
            return $this->response(false, [], "Failed to fetch {$action} events.");
        }
    }

    // ✅ Maintained for backward compatibility
    public function createdEvents(Request $request)
    {
        $request->merge(['type' => 'created']);
        return $this->userEvents($request);
    }

    // Approve a participant
    public function approveParticipant(Request $request, $eventId, $participantId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            $event = Event::findOrFail($eventId);

            if (!$this->isEventManager($event, $user->id)) {
                return $this->response(false, [], 'Unauthorized. Only event managers can approve participants.', 403);
            }

            $participant = EventParticipant::findOrFail($participantId);

            if ($participant->event_id !== $eventId) {
                return $this->response(false, [], 'Participant does not belong to this event.', 400);
            }

            $participant->update(['status' => 'joined']);

            // Notify the participant that they've been approved
            $participantUser = User::find($participant->user_id);
            if ($participantUser) {
                $participantUser->notify(new GeneralNotification([
                    'title' => 'Event Registration Approved',
                    'body' => 'Your registration for "' . $event->title . '" has been approved.',
                    'type' => 'event_registration_approved',
                    'extra' => [
                        'event_id' => $eventId,
                        'route' => "/events/$eventId"
                    ]
                ]));
            }

            return $this->response(true, [], 'Participant approved successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->response(false, [], 'Event or participant not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to approve participant: ' . $e->getMessage());
            return $this->response(false, [], 'Failed to approve participant.');
        }
    }

    // Generate volunteer certificate
    public function generateCertificate(Request $request, $eventId, $participantId)
    {
        if (!$user = $this->checkAuth($request)) {
            return $user;
        }

        try {
            $event = Event::findOrFail($eventId);

            if (!$this->isEventManager($event, $user->id)) {
                return $this->response(false, [], 'Unauthorized. Only event managers can generate certificates.', 403);
            }

            if (!$event->provides_certificate) {
                return $this->response(false, [], 'This event does not provide certificates.', 400);
            }

            $participant = EventParticipant::findOrFail($participantId);

            if ($participant->event_id !== $eventId || $participant->status !== 'joined') {
                $errorMsg = $participant->event_id !== $eventId
                    ? 'Participant does not belong to this event.'
                    : 'Participant must be approved to receive a certificate.';
                return $this->response(false, [], $errorMsg, 400);
            }

            $participant->update(['has_certificate' => true]);

            return $this->response(
                true,
                ['certificate_url' => "/certificates/{$eventId}/{$participantId}"],
                'Certificate generated successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->response(false, [], 'Event or participant not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to generate certificate: ' . $e->getMessage());
            return $this->response(false, [], 'Failed to generate certificate.');
        }
    }

    private function notifyCommunityMembersAboutEvent($communityId, $event)
    {
        try {
            $members = \App\Models\VitalAid\CommunityMember::where('community_id', $communityId)
                ->where('status', 'active')
                ->with('user')
                ->get();

            if ($members->isEmpty()) {
                return;
            }

            $notificationData = [
                'title' => 'New Event: ' . $event->title,
                'body' => 'A new event has been created in your community.',
                'type' => 'new_event',
                'extra' => [
                    'event_id' => $event->id,
                    'community_id' => $communityId,
                    'start_time' => $event->start_time,
                    'location' => $event->location
                ]
            ];

            foreach ($members as $member) {
                if ($member->user_id !== $communityId) {
                    $member->user->notify(new GeneralNotification($notificationData));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify community members about event: ' . $e->getMessage());
        }
    }
}
