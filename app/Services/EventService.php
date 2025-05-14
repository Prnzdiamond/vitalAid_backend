<?php

namespace App\Services;

use App\Models\User;
use App\Models\VitalAid\Event;
use App\Models\VitalAid\EventParticipant;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventService
{
    /**
     * Mark an event as completed and notify participants
     *
     * @param string $eventId
     * @return bool
     */
    public static function completeEvent(string $eventId): bool
    {
        try {
            $event = Event::findOrFail($eventId);

            // Only change status if not already completed
            if ($event->status !== 'completed') {
                // Update event status to completed
                $event->update(['status' => 'completed']);

                // Notify all participants
                self::notifyParticipantsForFeedback($event);

                Log::info("Event {$eventId} ({$event->title}) marked as completed successfully");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to complete event: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify all participants that the event has ended and request feedback
     *
     * @param Event $event
     * @return void
     */
    public static function notifyParticipantsForFeedback(Event $event): void
    {
        try {
            // Get all approved participants
            $participants = EventParticipant::where('event_id', $event->_id)
                ->where('status', 'joined')
                ->get();

            $count = 0;
            foreach ($participants as $participant) {
                $user = User::find($participant->user_id);

                if ($user) {
                    // Send notification
                    $user->notify(new GeneralNotification([
                        'title' => 'Event Completed: ' . $event->title,
                        'body' => 'The event has ended. We would appreciate your feedback!',
                        'type' => 'event_feedback_request',
                        'extra' => [
                            'route' => "/events/{$event->_id}",
                            'event_id' => $event->_id,
                            'event_title' => $event->title
                        ]
                    ]));
                    $count++;
                }
            }

            Log::info("Sent completion notifications for event {$event->_id} to {$count} participants");
        } catch (\Exception $e) {
            Log::error('Failed to send feedback notifications: ' . $e->getMessage());
        }
    }

    /**
     * Check for events that need to be marked as completed
     * This is called by a scheduled task
     *
     * @return void
     */
    public static function checkAndCompleteEvents(): void
    {
        try {
            Log::info('Starting checkAndCompleteEvents scheduler job');

            // Find events that have ended but are still marked as 'upcoming'
            $endedEvents = Event::where('status', 'upcoming')
                ->where('end_time', '<=', now())
                ->get();

            Log::info('Found ' . $endedEvents->count() . ' events to complete');

            foreach ($endedEvents as $event) {
                Log::info("Processing event for completion: {$event->_id} - {$event->title}");
                self::completeEvent($event->_id);
            }

            // Also notify about upcoming events starting soon (24 hours before)
            self::notifyUpcomingEvents();

            Log::info('Finished checkAndCompleteEvents scheduler job');
        } catch (\Exception $e) {
            Log::error('Failed to check and complete events: ' . $e->getMessage());
        }
    }

    /**
     * Notify participants about events starting soon
     *
     * @return void
     */
    public static function notifyUpcomingEvents(): void
    {
        try {
            $tomorrow = Carbon::now()->addDay();
            $todayPlus30 = Carbon::now()->addMinutes(30);

            // Events starting in approximately 24 hours
            $dayBeforeEvents = Event::where('status', 'upcoming')
                ->whereBetween('start_time', [
                    $tomorrow->copy()->subMinutes(30),
                    $tomorrow->copy()->addMinutes(30)
                ])
                ->get();

            // Events starting in 30 minutes
            $soonEvents = Event::where('status', 'upcoming')
                ->whereBetween('start_time', [
                    Carbon::now(),
                    $todayPlus30
                ])
                ->get();

            Log::info('Found ' . $dayBeforeEvents->count() . ' events starting in ~24 hours');
            Log::info('Found ' . $soonEvents->count() . ' events starting in ~30 minutes');

            // Notify for day-before events
            foreach ($dayBeforeEvents as $event) {
                self::notifyEventStartingSoon($event, '24 hours');
            }

            // Notify for soon events
            foreach ($soonEvents as $event) {
                self::notifyEventStartingSoon($event, '30 minutes');
            }

        } catch (\Exception $e) {
            Log::error('Error in notifyUpcomingEvents: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications to event participants about an upcoming event
     *
     * @param Event $event
     * @param string $timeframe
     * @return void
     */
    public static function notifyEventStartingSoon(Event $event, string $timeframe): void
    {
        try {
            $participants = EventParticipant::where('event_id', $event->_id)
                ->where('status', 'joined')
                ->get();

            foreach ($participants as $participant) {
                $user = User::find($participant->user_id);

                if ($user) {
                    $user->notify(new GeneralNotification([
                        'title' => 'Event Starting Soon: ' . $event->title,
                        'body' => "Your event starts in approximately {$timeframe} at {$event->location}.",
                        'type' => 'event_starting_soon',
                        'extra' => [
                            'route' => "/events/{$event->_id}",
                            'event_id' => $event->_id,
                            'event_title' => $event->title,
                            'start_time' => $event->start_time,
                            'location' => $event->location
                        ]
                    ]));
                }
            }

            // Also notify the event manager
            $eventManager = User::find($event->event_manager);
            if ($eventManager) {
                $eventManager->notify(new GeneralNotification([
                    'title' => 'Your Event Starting Soon: ' . $event->title,
                    'body' => "The event you're managing starts in approximately {$timeframe} at {$event->location}. Don't forget to prepare!",
                    'type' => 'event_manager_reminder',
                    'extra' => [
                        'route' => "/events/{$event->_id}",
                        'event_id' => $event->_id,
                        'event_title' => $event->title,
                        'start_time' => $event->start_time,
                        'location' => $event->location
                    ]
                ]));
            }

            Log::info("Sent {$timeframe} notifications for event {$event->_id} - {$event->title}");
        } catch (\Exception $e) {
            Log::error("Failed to send event starting soon notifications for {$event->_id}: " . $e->getMessage());
        }
    }

    /**
     * Check if an ongoing event has ended
     *
     * @param Event $event
     * @return bool
     */
    public static function hasEventEnded(Event $event): bool
    {
        return $event->status === 'completed' || now() > $event->end_time;
    }
}
