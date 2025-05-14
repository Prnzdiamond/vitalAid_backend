<?php

namespace App\Http\Controllers\VitalAid;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\VitalAid\EventResource;
use App\Http\Resources\VitalAid\DonationResource;
use App\Http\Resources\VitalAid\ConsultationResource;
use App\Http\Resources\VitalAid\DonationRequestResource;

class DashboardController extends Controller
{
    /**
     * Get user dashboard data based on their role
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $dashboardData = $user->getDashboardData();

        // Process the data based on role to add counts and transform with resources
        $processedData = $this->processDataBasedOnRole($user->role, $dashboardData);

        return response()->json([
            'success' => true,
            'data' => $processedData
        ]);
    }

    /**
     * Process dashboard data based on user role
     *
     * @param string $role
     * @param array $data
     * @return array
     */
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

    /**
     * Process regular user dashboard data
     *
     * @param array $data
     * @return array
     */
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

    /**
     * Process health expert dashboard data
     *
     * @param array $data
     * @return array
     */
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

    /**
     * Process community dashboard data
     *
     * @param array $data
     * @return array
     */
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
}
