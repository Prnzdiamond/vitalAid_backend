<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\VitalAid\Rating;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\VitalAid\Consultation;

class AdminConsultationController extends Controller
{
    public function index(Request $request)
    {
        $query = Consultation::with(['user', 'doctor']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('handled_by')) {
            $query->where('handled_by', $request->handled_by);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('user', function ($sq) use ($searchTerm) {
                    $sq->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name', 'like', "%{$searchTerm}%");
                })->orWhereHas('doctor', function ($sq) use ($searchTerm) {
                    $sq->where('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name', 'like', "%{$searchTerm}%");
                });
            });
        }

        $consultations = $query->latest('last_message_at')->paginate(20);

        // Get statistics
        $totalConsultations = Consultation::count();
        $activeConsultations = Consultation::where('status', 'in_progress')->count();
        $completedConsultations = Consultation::where('status', 'completed')->count();
        $averageRating = Consultation::whereNotNull('rating')->avg('rating') ?? 0;

        return view('admin.consultations.index', compact(
            'consultations',
            'totalConsultations',
            'activeConsultations',
            'completedConsultations',
            'averageRating'
        ));
    }

    public function show($id)
    {
        $consultation = Consultation::with(['user', 'doctor', 'followUpRequestedBy'])->findOrFail($id);

        return view('admin.consultations.show', compact('consultation'));
    }


    public function doctorPerformance()
    {
        $doctors = User::where('role', 'health_expert')
            ->where('is_verified', true)
            ->get()
            ->map(function ($doctor) {
                // Count consultations handled by this doctor
                $consultationsCount = Consultation::where('doctor_id', $doctor->_id)->count();
                $doctor->consultations_handled_count = $consultationsCount;

                // Get average rating
                $doctor->average_rating = Consultation::getAverageRatingForDoctor($doctor->_id);

                // Count completed consultations
                $doctor->completed_consultations = Consultation::where('doctor_id', $doctor->_id)
                    ->where('status', 'completed')
                    ->count();

                // Count follow up requests
                $doctor->follow_up_requests = Consultation::where('doctor_id', $doctor->_id)
                    ->where('follow_up_requested', true)
                    ->count();

                return $doctor;
            })
            ->sortByDesc('consultations_handled_count');

        return view('admin.consultations.doctor-performance', compact('doctors'));
    }

    public function followUpRequests()
    {
        $followUpRequests = Consultation::with(['user', 'doctor', 'followUpRequestedBy'])
            ->where('follow_up_requested', true)
            ->orderBy('follow_up_requested_at', 'desc')
            ->paginate(15);

        return view('admin.consultations.follow-up-requests', compact('followUpRequests'));
    }

    public function analytics()
    {
        $monthlyConsultations = collect();
        $currentYear = Carbon::now()->year;

        // Generate monthly consultation counts
        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::create($currentYear, $month, 1)->startOfDay();
            $endOfMonth = Carbon::create($currentYear, $month, 1)->endOfMonth()->endOfDay();

            $count = Consultation::where('created_at', '>=', $startOfMonth)
                ->where('created_at', '<=', $endOfMonth)
                ->count();

            $monthlyConsultations->push([
                'month' => $month,
                'count' => $count
            ]);
        }

        // Status breakdown
        $statusBreakdown = collect();
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        foreach ($statuses as $status) {
            $count = Consultation::where('status', $status)->count();
            if ($count > 0) {
                $statusBreakdown->push((object) [
                    'status' => $status,
                    'count' => $count
                ]);
            }
        }

        // Completed consultations
        $completedConsultations = Consultation::where('status', 'completed')
            ->whereNotNull('doctor_id')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        // Top performing doctors
        $topDoctors = User::where('role', 'health_expert')
            ->get()
            ->map(function ($doctor) {
                $consultationsCount = Consultation::where('doctor_id', $doctor->_id)->count();
                $doctor->consultations_handled_count = $consultationsCount;
                $doctor->average_rating = Consultation::getAverageRatingForDoctor($doctor->_id);
                return $doctor;
            })
            ->sortByDesc('consultations_handled_count')
            ->take(10);

        return view('admin.consultations.analytics', compact(
            'monthlyConsultations',
            'statusBreakdown',
            'completedConsultations',
            'topDoctors'
        ));
    }

}