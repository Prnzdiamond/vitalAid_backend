<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class NotificationusageSample extends Controller
{



    /**
     * Send a general notification to a user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendGeneralNotification(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Here's an example of sending different types of notifications

        // For a simple notification:
        $user->notify(new GeneralNotification([
            'title' => 'Welcome to the Platform',
            'body' => 'We\'re glad to have you here. Check out your dashboard to get started.',
            'type' => 'general',
        ]));

        // For a notification with a route link:
        $user->notify(new GeneralNotification([
            'title' => 'New Feature Available',
            'body' => 'You can now export your consultation history. Try it out!',
            'type' => 'general',
            'extra' => [
                'route' => '/dashboard/settings'
            ]
        ]));

        // For a notification about a new message:
        $user->notify(new GeneralNotification([
            'title' => 'New Message',
            'body' => 'You have a new message from Dr. Smith',
            'type' => 'general',
            'extra' => [
                'route' => '/messages/inbox',
                'message_id' => 123
            ]
        ]));

        // For a notification about a completed task:
        $user->notify(new GeneralNotification([
            'title' => 'Task Completed',
            'body' => 'Your requested lab results have been processed',
            'type' => 'general',
            'extra' => [
                'route' => '/lab-results/123',
                'result_id' => 123,
                'status' => 'completed'
            ]
        ]));

        return response()->json(['message' => 'Notifications sent successfully']);
    }

    /**
     * Example of sending a notification from a different workflow,
     * such as when a document is uploaded
     */
    public function documentUploaded($userId, $documentId)
    {
        $user = User::findOrFail($userId);
        $document = Document::findOrFail($documentId);

        $user->notify(new GeneralNotification([
            'title' => 'Document Ready',
            'body' => "Your document '{$document->name}' has been processed and is ready for viewing.",
            'type' => 'general',
            'extra' => [
                'route' => "/documents/{$documentId}",
                'document_id' => $documentId,
                'document_type' => $document->type
            ]
        ]));

        return response()->json(['message' => 'Document notification sent successfully']);
    }

    /**
     * Example of sending a notification for appointment reminders
     */
    public function appointmentReminder($userId, $appointmentId)
    {
        $user = User::findOrFail($userId);
        $appointment = Appointment::findOrFail($appointmentId);

        // Format the date for display
        $appointmentDate = $appointment->scheduled_at->format('l, F j, Y');
        $appointmentTime = $appointment->scheduled_at->format('g:i A');

        $user->notify(new GeneralNotification([
            'title' => 'Upcoming Appointment Reminder',
            'body' => "You have an appointment scheduled for {$appointmentDate} at {$appointmentTime}.",
            'type' => 'general',
            'extra' => [
                'route' => "/appointments/{$appointmentId}",
                'appointment_id' => $appointmentId,
                'calendar_event' => [
                    'date' => $appointment->scheduled_at->toISOString(),
                    'duration' => $appointment->duration_minutes
                ]
            ]
        ]));

        return response()->json(['message' => 'Appointment reminder sent successfully']);
    }

    /**
     * Example of sending a notification for system updates or maintenance
     */
    public function systemNotification()
    {
        // Get all active users
        $users = User::where('active', true)->get();

        foreach ($users as $user) {
            $user->notify(new GeneralNotification([
                'title' => 'Scheduled Maintenance',
                'body' => 'The system will be undergoing maintenance on Sunday between 2-4 AM EST. Brief service interruptions may occur during this time.',
                'type' => 'general',
                'extra' => [
                    'priority' => 'medium',
                    'expires_at' => now()->addDays(5)->toISOString()
                ]
            ]));
        }

        return response()->json(['message' => 'System notification sent to all users']);
    }
}