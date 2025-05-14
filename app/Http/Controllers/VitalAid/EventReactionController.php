<?php

namespace App\Http\Controllers\VitalAid;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\VitalAid\Event;
use App\Models\VitalAid\EventReaction;
use App\Models\VitalAid\EventParticipant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\VitalAid\EventReactionResource;
use App\Notifications\GeneralNotification;

class EventReactionController extends Controller
{
    // Common response formats
    private function successResponse($data = [], $message = 'Success', $code = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function errorResponse($message = 'Error', $code = 500)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }

    // Auth check helper
    private function checkAuth(Request $request)
    {
        if (!$user = $request->user()) {
            return $this->errorResponse('Unauthorized.', 401);
        }
        return $user;
    }

    /**
     * Add reaction to an event (like/dislike with optional comment)
     */
    public function addReaction(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request))
            return $user;

        $validator = Validator::make($request->all(), [
            'reaction_type' => 'required|string|in:like,dislike',
            'comment' => 'sometimes|nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Event::findOrFail($eventId);

            // Check if event has ended
            if ($event->status !== 'completed') {
                return $this->errorResponse('You can only react to completed events.', 400);
            }

            // Check if user participated in this event
            $participated = EventParticipant::where('event_id', $eventId)
                ->where('user_id', $user->id)
                ->where('status', 'joined')
                ->exists();

            if (!$participated) {
                return $this->errorResponse('Only event participants can leave reactions.', 403);
            }

            // Check if user already reacted, if so, update their reaction
            $existingReaction = EventReaction::where('event_id', $eventId)
                ->where('user_id', $user->id)
                ->first();

            if ($existingReaction) {
                $existingReaction->update([
                    'reaction_type' => $request->reaction_type,
                    'comment' => $request->comment
                ]);

                // Eager load the user for the resource
                $existingReaction->load('user');

                return $this->successResponse(
                    ['reaction' => new EventReactionResource($existingReaction)],
                    'Your reaction has been updated.'
                );
            }

            // Create new reaction
            $reaction = EventReaction::create([
                'event_id' => $eventId,
                'user_id' => $user->id,
                'reaction_type' => $request->reaction_type,
                'comment' => $request->comment
            ]);

            // Eager load the user for the resource
            $reaction->load('user');

            return $this->successResponse(
                ['reaction' => new EventReactionResource($reaction)],
                'Your reaction has been recorded.',
                201
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Event not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to add reaction to event ' . $eventId . ': ' . $e->getMessage());
            return $this->errorResponse('Failed to add reaction.');
        }
    }

    /**
     * Get all reactions for an event
     */
    public function getEventReactions($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);

            // Eager load users to prevent N+1 queries
            $reactions = EventReaction::where('event_id', $eventId)
                ->with('user')
                ->get();

            return $this->successResponse([
                'event_id' => $eventId,
                'event_title' => $event->title,
                'likes_count' => EventReaction::countLikes($eventId),
                'dislikes_count' => EventReaction::countDislikes($eventId),
                'reactions' => EventReactionResource::collection($reactions)
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Event not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to get reactions for event ' . $eventId . ': ' . $e->getMessage());
            return $this->errorResponse('Failed to get reactions.');
        }
    }

    /**
     * Delete a user's reaction
     */
    public function deleteReaction(Request $request, $eventId)
    {
        if (!$user = $this->checkAuth($request))
            return $user;

        try {
            $reaction = EventReaction::where('event_id', $eventId)
                ->where('user_id', $user->id)
                ->first();

            if (!$reaction) {
                return $this->errorResponse('Reaction not found.', 404);
            }

            $reaction->delete();
            return $this->successResponse([], 'Reaction removed successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete reaction: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete reaction.');
        }
    }

    /**
     * Get reaction summary for an event
     */
    public function getReactionSummary($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);

            return $this->successResponse([
                'event_id' => $eventId,
                'event_title' => $event->title,
                'likes_count' => EventReaction::countLikes($eventId),
                'dislikes_count' => EventReaction::countDislikes($eventId),
                'total_reactions' => EventReaction::where('event_id', $eventId)->count(),
                'comments_count' => EventReaction::where('event_id', $eventId)
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->count()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Event not found.', 404);
        } catch (\Exception $e) {
            Log::error('Failed to get reaction summary for event ' . $eventId . ': ' . $e->getMessage());
            return $this->errorResponse('Failed to get reaction summary.');
        }
    }
}
