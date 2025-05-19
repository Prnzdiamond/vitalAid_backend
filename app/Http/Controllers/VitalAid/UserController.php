<?php

namespace App\Http\Controllers\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\VitalAid\EventResource;
use App\Http\Resources\VitalAid\DonationResource;
use App\Http\Resources\VitalAid\ConsultationResource;
use App\Http\Resources\VitalAid\DonationRequestResource;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $dashboardData = $user->getDashboardData();

            // Process the data based on role to add counts and transform with resources
            $processedData = $this->processDataBasedOnRole($user->role, $dashboardData);

            return response()->json([
                'success' => true,
                'data' => $processedData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch dashboard data: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data.'
            ], 500);
        }
    }

    private function processDataBasedOnRole(string $role, array $data): array
    {
        switch ($role) {
            case 'user':
                return $this->processUserData($data);
            case 'health_expert':
                return $this->processHealthExpertData($data);
            case 'charity':
                return $this->processCharityData($data);
            case 'community':
                return $this->processCommunityData($data);
            default:
                return [];
        }
    }


    private function processUserData(array $data): array
    {
        return [
            'consultations_count' => $data['consultations_count'],
            'donations_count' => $data['donations_count'],
            'events_attended_count' => $data['events_attended_count'],
            'upcoming_events_count' => $data['upcoming_events_count'],

            'consultations_requested' => ConsultationResource::collection($data['consultations_requested']),
            'donations_made' => DonationResource::collection($data['donations_made']),
            'events_attended' => $data['events_attended']->map(function ($event) {
                return new EventResource($event);
            }),
            'upcoming_events' => $data['upcoming_events']->map(function ($event) {
                return new EventResource($event);
            }),
            'first_three_upcoming_events' => $data['first_three_upcoming_events']->map(function ($event) {
                return new EventResource($event);
            }),
            'first_three_recent_consultations' => ConsultationResource::collection($data['first_three_recent_consultations']),
        ];
    }


    private function processHealthExpertData(array $data): array
    {
        return [
            'consultations_handled_count' => $data['consultations_handled_count'],
            'active_consultations_count' => $data['active_consultations_count'],
            'donations_made_count' => $data['donations_made_count'],
            'events_attended_count' => $data['events_attended_count'],
            'upcoming_events_count' => $data['upcoming_events_count'],

            'consultations_handled' => ConsultationResource::collection($data['consultations_handled']),
            'active_requested_consultations' => ConsultationResource::collection($data['active_requested_consultations']),
            'average_rating' => $data['average_rating'],
            'donations_made' => DonationResource::collection($data['donations_made']),
            'events_attended' => $data['events_attended']->map(function ($event) {
                return new EventResource($event);
            }),
            'upcoming_events' => $data['upcoming_events']->map(function ($event) {
                return new EventResource($event);
            }),
            'first_three_recent_consultations_accepted' => ConsultationResource::collection($data['first_three_recent_consultations_accepted']),
        ];
    }


    private function processCharityData(array $data): array
    {
        return [
            'donations_received_count' => $data['donations_received']->count(),
            'ongoing_donations_count' => $data['ongoing_donations']->count(),
            'upcoming_events_count' => $data['upcoming_events']->count(),

            'donations_received' => DonationResource::collection($data['donations_received']),
            'total_amount_raised' => $data['total_amount_raised'],
            'ongoing_donations' => DonationRequestResource::collection($data['ongoing_donations']),
            'upcoming_events' => $data['upcoming_events']->map(function ($event) {
                return new EventResource($event);
            }),
            'first_three_ongoing_donations' => DonationRequestResource::collection($data['first_three_ongoing_donations']),
            'first_three_upcoming_events' => $data['first_three_upcoming_events']->map(function ($event) {
                return new EventResource($event);
            }),
        ];
    }


    private function processCommunityData(array $data): array
    {
        return [
            'members_count' => $data['members_count'],
            'events_hosted_count' => $data['events_hosted_count'],
            'events_hosted_this_year_count' => $data['events_hosted_this_year_count'],
            'upcoming_events_count' => $data['upcoming_events_count'],

            'members' => $data['members'], // Will need a resource when implemented
            'events_hosted_this_year' => EventResource::collection($data['events_hosted_this_year']),
            'upcoming_events' => EventResource::collection($data['upcoming_events']),
            'first_three_upcoming_events' => EventResource::collection($data['first_three_upcoming_events']),
            'top_three_events_hosted' => EventResource::collection($data['top_three_events_hosted']),
        ];
    }

    /**
     * Get user notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_notifications' => $user->unreadNotifications
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch notifications: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications.'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function markNotificationAsRead(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found.'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read.'
            ], 500);
        }
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        $user = $request->user(); // or Auth::user()

        if ($user) {
            $user->unreadNotifications->markAsRead();
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found.'
        ], 404);
    }


    /**
     * Get user donations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function userDonations(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $donations = $user->donations()->get(); // Fixed method name from donation() to donations()

            return response()->json([
                'success' => true,
                'data' => [
                    'donations' => $donations
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch user donations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user donations.'
            ], 500);
        }
    }

    /**
     * Get recent user activities
     * Currently commented out in the original code, but included here for completeness
     *
     * @param Request $request
     * @return JsonResponse
     */
    /*
    public function recentActivities(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $activities = $user->activities()->latest()->take(10)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'activities' => $activities
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to fetch recent activities: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activities.'
            ], 500);
        }
    }
    */
}