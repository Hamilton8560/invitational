<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Checkin;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    /**
     * Handle QR code scan and process check-in
     */
    public function scan(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Decrypt and validate the QR code token
            $payload = decrypt($request->input('token'));

            // Validate required fields
            if (! isset($payload['sale_id'], $payload['user_id'], $payload['event_id'], $payload['type'])) {
                ActivityLog::log('checkin_failed', null, [
                    'reason' => 'Invalid QR code payload',
                    'token' => $request->input('token'),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code',
                ], 400);
            }

            // Load the sale with relationships
            $sale = Sale::with(['user', 'event', 'product', 'team', 'individualPlayer', 'booth', 'banner'])
                ->findOrFail($payload['sale_id']);

            // Verify sale is completed
            if ($sale->status !== 'completed') {
                ActivityLog::log('checkin_failed', $sale, [
                    'reason' => 'Sale not completed',
                    'status' => $sale->status,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'This registration is not completed',
                ], 400);
            }

            // Check if already checked in
            $existingCheckin = Checkin::where('sale_id', $sale->id)->first();
            if ($existingCheckin && ! $request->user()->can('override_checkin')) {
                ActivityLog::log('checkin_duplicate', $sale, [
                    'original_checkin_id' => $existingCheckin->id,
                    'original_checkin_at' => $existingCheckin->checked_in_at,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Already checked in',
                    'checkin' => $existingCheckin,
                ], 409);
            }

            // Process check-in within transaction
            $checkin = DB::transaction(function () use ($sale, $payload, $request, $existingCheckin) {
                // If override, delete existing check-in
                if ($existingCheckin) {
                    $existingCheckin->delete();
                    ActivityLog::log('checkin_override', $sale, [
                        'overridden_checkin_id' => $existingCheckin->id,
                    ]);
                }

                // Create new check-in
                $checkin = Checkin::create([
                    'sale_id' => $sale->id,
                    'event_id' => $sale->event_id,
                    'user_id' => $sale->user_id,
                    'checked_in_by' => $request->user()->id,
                    'checked_in_at' => now(),
                    'check_in_type' => $payload['type'],
                    'team_id' => $payload['team_id'] ?? null,
                    'individual_player_id' => $payload['individual_player_id'] ?? null,
                    'booth_id' => $payload['booth_id'] ?? null,
                    'banner_id' => $payload['banner_id'] ?? null,
                ]);

                ActivityLog::log('checkin_success', $checkin, [
                    'type' => $payload['type'],
                    'sale_id' => $sale->id,
                ]);

                return $checkin;
            });

            // Load relationships for response
            $checkin->load(['sale', 'event', 'user', 'checkedInBy', 'team', 'individualPlayer', 'booth', 'banner']);

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful',
                'checkin' => $checkin,
                'registration' => [
                    'name' => $sale->user->name,
                    'email' => $sale->user->email,
                    'type' => $payload['type'],
                    'product' => $sale->product->name,
                    'team' => $sale->team?->name,
                    'player' => $sale->individualPlayer?->name,
                    'booth' => $sale->booth?->name,
                    'banner' => $sale->banner?->name,
                ],
            ]);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            ActivityLog::log('checkin_failed', null, [
                'reason' => 'Invalid encryption',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid or corrupted QR code',
            ], 400);
        } catch (\Exception $e) {
            ActivityLog::log('checkin_error', null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during check-in',
            ], 500);
        }
    }

    /**
     * Search for registrations by name, email, or confirmation number
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('search_registrations');

        $request->validate([
            'query' => 'required|string|min:2',
            'event_id' => 'required|exists:events,id',
        ]);

        $query = $request->input('query');
        $eventId = $request->input('event_id');

        $sales = Sale::with(['user', 'product', 'team', 'individualPlayer', 'booth', 'banner'])
            ->where('event_id', $eventId)
            ->where('status', 'completed')
            ->where(function ($q) use ($query) {
                $q->whereHas('user', function ($userQuery) use ($query) {
                    $userQuery->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                })
                    ->orWhere('id', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        ActivityLog::log('registration_search', null, [
            'query' => $query,
            'event_id' => $eventId,
            'results_count' => $sales->count(),
        ]);

        return response()->json([
            'success' => true,
            'registrations' => $sales->map(function ($sale) {
                $checkin = Checkin::where('sale_id', $sale->id)->first();

                return [
                    'sale_id' => $sale->id,
                    'name' => $sale->user->name,
                    'email' => $sale->user->email,
                    'product' => $sale->product->name,
                    'type' => $this->determineType($sale),
                    'checked_in' => $checkin ? true : false,
                    'checked_in_at' => $checkin?->checked_in_at,
                    'qr_token' => encrypt([
                        'sale_id' => $sale->id,
                        'user_id' => $sale->user_id,
                        'event_id' => $sale->event_id,
                        'type' => $this->determineType($sale),
                        'team_id' => $sale->team_id,
                        'individual_player_id' => $sale->individual_player_id,
                        'booth_id' => $sale->booth_id,
                        'banner_id' => $sale->banner_id,
                    ]),
                ];
            }),
        ]);
    }

    /**
     * Determine check-in type from sale
     */
    protected function determineType(Sale $sale): string
    {
        if ($sale->team_id) {
            return 'team';
        }

        if ($sale->individual_player_id) {
            return 'individual';
        }

        if ($sale->booth_id) {
            return 'vendor';
        }

        if ($sale->banner_id) {
            return 'vendor';
        }

        return 'spectator';
    }
}
