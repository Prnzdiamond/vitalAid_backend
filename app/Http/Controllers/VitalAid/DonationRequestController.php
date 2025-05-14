<?php

namespace App\Http\Controllers\VitalAid;

use App\Http\Resources\VitalAid\DonationRequestResource;
use App\Http\Resources\VitalAid\DonationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\VitalAid\DonationRequest;
use Illuminate\Support\Facades\Validator;

class DonationRequestController extends Controller
{
    public function index()
    {
        try {
            $requests = DonationRequest::all();
            $requests = DonationRequestResource::collection($requests);
            return response()->json([
                'success' => true,
                'data' => [
                    'requests' => $requests
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch donation requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch donation requests.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Starting donation request creation');
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'amount_needed' => 'required|numeric|min:0.01',
            'category' => 'nullable|string',
            'is_urgent' => 'nullable',
            'banner_image' => 'nullable|image|max:2048', // Adjust mime types and size as needed
            'other_images' => 'nullable|array',
            'other_images.*' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        try {
            log::info("createteteeett");
            $donationRequestData = $request->only(['title', 'description', 'amount_needed', 'category', 'is_urgent']);
            $donationRequestData['org_id'] = $user->id;
            $donationRequestData['amount_received'] = 0;
            $donationRequestData['status'] = 'pending';

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                $banner = $request->file('banner_image');
                $bannerName = time() . '_' . $banner->getClientOriginalName();
                $banner->storeAs('donation_banners', $bannerName, 'public');
                $donationRequestData['banner_url'] = asset(Storage::url('donation_banners/' . $bannerName));
            }

            // Handle other images upload
            if ($request->hasFile('other_images')) {
                $otherImages = [];
                foreach ($request->file('other_images') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->storeAs('donation_other_images', $imageName, 'public');
                    $otherImages[] = asset(Storage::url('donation_other_images/' . $imageName));
                }
                $donationRequestData['other_images'] = $otherImages;
            }

            $donationRequest = DonationRequest::create($donationRequestData);

            $donationRequest = new DonationRequestResource($donationRequest);

            return response()->json(['success' => true, 'message' => 'Donation request created.', 'data' => ['request' => $donationRequest]], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create donation request: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create donation request.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $request = DonationRequest::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'request' => new DonationRequestResource($request)
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donation request not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch donation request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch donation request.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'amount_needed' => 'nullable|numeric|min:0.01',
            'category' => 'nullable|string',
            'is_urgent' => 'nullable|boolean',
            'banner_image' => 'nullable|image|max:2048',
            'other_images' => 'nullable|array',
            'other_images.*' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        try {
            $donationRequest = DonationRequest::findOrFail($id);
            $user = $request->user();

            if ($donationRequest->org_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            if ($donationRequest->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Cannot update funded/rejected request.'], 400);
            }

            $donationRequestData = $request->only(['title', 'description', 'amount_needed', 'category', 'is_urgent']);

            // Handle banner image update
            if ($request->hasFile('banner_image')) {
                // Delete old banner if exists
                if ($donationRequest->banner_url && !str_contains($donationRequest->banner_url, 'data:')) {
                    $oldPath = str_replace(asset('/storage/'), 'public/', $donationRequest->banner_url);
                    if (Storage::exists($oldPath)) {
                        Storage::delete($oldPath);
                    }
                }
                $banner = $request->file('banner_image');
                $bannerName = time() . '_' . $banner->getClientOriginalName();
                $banner->storeAs('donation_banners', $bannerName, 'public');
                $donationRequestData['banner_url'] = asset(Storage::url('donation_banners/' . $bannerName));
            }

            // Handle other images update (append new ones)
            if ($request->hasFile('other_images')) {
                $otherImages = $donationRequest->other_images ?? [];
                foreach ($request->file('other_images') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->storeAs('donation_other_images', $imageName, 'public');
                    $otherImages[] = asset(Storage::url('donation_other_images/' . $imageName));
                }
                $donationRequestData['other_images'] = $otherImages;
            }

            $donationRequest->update($donationRequestData);

            return response()->json(['success' => true, 'message' => 'Donation request updated.', 'data' => ['request' => new DonationRequestResource($donationRequest)]], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Donation request not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to update donation request: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update donation request.'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $donationRequest = DonationRequest::findOrFail($id);
            $user = $request->user();

            if ($donationRequest->org_id !== $user->id || $donationRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized or request not deletable.'
                ], 403);
            }

            $donationRequest->delete();
            return response()->json([
                'success' => true,
                'message' => 'Donation request deleted.'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donation request not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete donation request: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete donation request.'
            ], 500);
        }
    }

    public function getOrganizationRequests(Request $request)
    {
        $user = $request->user();

        // if ($user->role !== 'charity') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Unauthorized.'
        //     ], 403);
        // }

        try {
            $requests = $user->donationRequests()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'requests' => DonationRequestResource::collection($requests)
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch organization donation requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch organization donation requests.'
            ], 500);
        }
    }

    public function getRequestDonations(Request $request, $id)
    {
        $user = $request->user();

        try {
            $donationRequest = DonationRequest::findOrFail($id);

            if ($donationRequest->org_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 403);
            }

            $donations = $donationRequest->donations()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'donations' => new DonationResource($donations)
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donation request not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch donations for request ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch donations for this request.'
            ], 500);
        }
    }

    public function markAsCompleted(Request $request, $id)
    {
        $user = $request->user();

        try {
            $donationRequest = DonationRequest::findOrFail($id);

            if ($donationRequest->org_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 403);
            }

            if ($donationRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark as completed.'
                ], 400);
            }

            $donationRequest->status = 'completed';
            $donationRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Donation request marked as completed.',
                'data' => [
                    'request' => new DonationRequestResource($donationRequest)
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donation request not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to mark donation request as completed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark donation request as completed.'
            ], 500);

        }
    }
}
