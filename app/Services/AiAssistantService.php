<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Parcel;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiAssistantService
{
    public function answer(User $user, string $question): string
    {
        if ($this->hasExternalProvider()) {
            $external = $this->externalAnswer($user, $question);
            if ($external) {
                return $external;
            }
        }

        return $this->localAnswer($user, $question);
    }

    private function hasExternalProvider(): bool
    {
        return config('services.ai_assistant.provider') !== 'local'
            && filled(config('services.ai_assistant.api_key'));
    }

    private function externalAnswer(User $user, string $question): ?string
    {
        if (config('services.ai_assistant.provider') === 'openai') {
            return $this->openAiAnswer($user, $question);
        }

        try {
            $context = $this->contextFor($user, $question);

            $response = Http::withToken((string) config('services.ai_assistant.api_key'))
                ->timeout(12)
                ->post((string) config('services.ai_assistant.endpoint'), [
                    'model' => config('services.ai_assistant.model'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are Village Link courier support. Give short, practical answers based on the provided customer data.'],
                        ['role' => 'user', 'content' => json_encode($context)],
                    ],
                ]);

            return data_get($response->json(), 'choices.0.message.content');
        } catch (\Throwable) {
            return null;
        }
    }

    private function openAiAnswer(User $user, string $question): ?string
    {
        try {
            $context = $this->contextFor($user, $question);

            $response = Http::withToken((string) config('services.ai_assistant.api_key'))
                ->acceptJson()
                ->timeout(20)
                ->post((string) config('services.ai_assistant.endpoint'), [
                    'model' => config('services.ai_assistant.model'),
                    'input' => [
                        [
                            'role' => 'system',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => 'You are Village Link AI Assistant inside a courier and parcel tracking system. Answer only about Village Link workflows, the current user data, parcel status, payments, drivers, complaints, reports, account help, and setup status. Be clear, short, and action-oriented. If the user asks for private data not in context, say you cannot access it.',
                            ]],
                        ],
                        [
                            'role' => 'user',
                            'content' => [[
                                'type' => 'input_text',
                                'text' => json_encode($context),
                            ]],
                        ],
                    ],
                    'max_output_tokens' => 450,
                ]);

            $json = $response->json();
            $text = data_get($json, 'output_text');

            if ($text) {
                return trim($text);
            }

            foreach ((array) data_get($json, 'output', []) as $item) {
                foreach ((array) data_get($item, 'content', []) as $content) {
                    $candidate = data_get($content, 'text');
                    if ($candidate) {
                        return trim($candidate);
                    }
                }
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function contextFor(User $user, string $question): array
    {
        $role = $user->role;
        $isDriver = in_array($role, ['driver', 'agent'], true);
        $isAdmin = $role === 'admin';

        $parcelQuery = Parcel::with('agent.driverProfile', 'latestLocation');

        if ($isDriver) {
            $parcelQuery->where('agent_id', $user->id);
        } elseif (! $isAdmin) {
            $parcelQuery->where('user_id', $user->id);
        }

        $recentParcels = $parcelQuery
            ->latest()
            ->take(10)
            ->get()
            ->map(fn (Parcel $parcel) => [
                'tracking_id' => $parcel->tracking_id,
                'sender' => $parcel->sender_name,
                'receiver' => $parcel->receiver_name,
                'status' => $parcel->status,
                'payment_status' => $parcel->payment_status ?? 'unpaid',
                'driver' => $parcel->agent?->name,
                'latest_gps' => $parcel->current_lat ? [$parcel->current_lat, $parcel->current_lng] : null,
                'pickup' => $parcel->pickup_location,
                'delivery' => $parcel->delivery_location,
                'eta' => $parcel->estimated_delivery_at?->format('Y-m-d H:i'),
            ]);

        $paymentQuery = Payment::query();
        if (! $isAdmin) {
            $paymentQuery->where('user_id', $user->id);
        }

        $context = [
            'question' => $question,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
            ],
            'system_capabilities' => [
                'customer' => ['book parcel', 'track parcel', 'live route map', 'vehicle GPS marker', 'pay online when available', 'bank transfer', 'cash payment', 'complaints', 'AI assistant', 'invoice PDF'],
                'driver' => ['profile', 'availability', 'assigned deliveries', 'live GPS sharing', 'status update', 'delivery proof upload'],
                'admin' => ['parcel management', 'driver management', 'payment verification', 'complaint replies', 'reports CSV export', 'user management'],
                'external_services' => [
                    'online_payment_available' => (bool) config('services.payhere.enabled'),
                    'email_mailer' => config('mail.default'),
                    'ai_provider' => config('services.ai_assistant.provider'),
                    'ai_external_key_present' => filled(config('services.ai_assistant.api_key')),
                    'map_provider' => config('services.maps.provider'),
                ],
            ],
            'recent_parcels' => $recentParcels,
            'active_complaints' => $isAdmin
                ? Complaint::whereIn('status', ['open', 'in_progress'])->count()
                : Complaint::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress'])->count(),
            'recent_payments' => $paymentQuery->latest()->take(6)->get(['amount', 'currency', 'method', 'status', 'reference']),
        ];

        if ($isAdmin) {
            $context['admin_live_system'] = [
                'parcel_totals' => [
                    'total' => Parcel::count(),
                    'active' => Parcel::where('status', '!=', 'delivered')->count(),
                    'delivered' => Parcel::where('status', 'delivered')->count(),
                    'pending_pickup' => Parcel::where('status', 'pending_pickup')->count(),
                    'out_for_delivery' => Parcel::where('status', 'out_for_delivery')->count(),
                ],
                'payments' => [
                    'pending_verification' => Payment::where('status', 'pending')->count(),
                    'paid_total_amount' => Payment::where('status', 'paid')->sum('amount'),
                    'failed' => Payment::where('status', 'failed')->count(),
                ],
                'drivers' => [
                    'total' => User::whereIn('role', ['driver', 'agent'])->count(),
                    'available' => User::whereIn('role', ['driver', 'agent'])->whereHas('driverProfile', fn ($query) => $query->where('availability_status', 'available'))->count(),
                    'busy' => User::whereIn('role', ['driver', 'agent'])->whereHas('driverProfile', fn ($query) => $query->where('availability_status', 'busy'))->count(),
                    'offline' => User::whereIn('role', ['driver', 'agent'])->whereHas('driverProfile', fn ($query) => $query->where('availability_status', 'offline'))->count(),
                ],
                'complaints' => [
                    'open' => Complaint::where('status', 'open')->count(),
                    'in_progress' => Complaint::where('status', 'in_progress')->count(),
                    'resolved' => Complaint::where('status', 'resolved')->count(),
                ],
            ];
        } elseif ($isDriver) {
            $context['driver_live_work'] = [
                'active_assigned_deliveries' => Parcel::where('agent_id', $user->id)->where('status', '!=', 'delivered')->count(),
                'delivered_total' => Parcel::where('agent_id', $user->id)->where('status', 'delivered')->count(),
                'profile' => $user->driverProfile?->only(['phone', 'vehicle_type', 'vehicle_number', 'availability_status', 'last_location_at']),
            ];
        } else {
            $context['customer_live_account'] = [
                'my_total_parcels' => Parcel::where('user_id', $user->id)->count(),
                'my_active_parcels' => Parcel::where('user_id', $user->id)->where('status', '!=', 'delivered')->count(),
                'my_delivered_parcels' => Parcel::where('user_id', $user->id)->where('status', 'delivered')->count(),
                'my_unpaid_parcels' => Parcel::where('user_id', $user->id)->where(function ($query) {
                    $query->whereNull('payment_status')->orWhere('payment_status', '!=', 'paid');
                })->count(),
            ];
        }

        return $context;
    }

    private function localAnswer(User $user, string $question): string
    {
        $q = Str::lower($question);
        preg_match('/trk[0-9a-z]+/i', $question, $match);
        $role = $user->role;
        $isDriver = in_array($role, ['driver', 'agent'], true);
        $isAdmin = $role === 'admin';

        if ($match) {
            $parcelQuery = Parcel::where('tracking_id', Str::upper($match[0]));

            if ($isDriver) {
                $parcelQuery->where('agent_id', $user->id);
            } elseif (! $isAdmin) {
                $parcelQuery->where('user_id', $user->id);
            }

            $parcel = $parcelQuery->first();

            if ($parcel) {
                $eta = $parcel->estimated_delivery_at ? ' ETA: '.$parcel->estimated_delivery_at->format('M d, g:i A').'.' : '';
                return 'Parcel '.$parcel->tracking_id.' is currently '.str_replace('_', ' ', $parcel->status).'. Payment: '.($parcel->payment_status ?? 'unpaid').'.'.$eta;
            }

            return 'I could not find that tracking ID in your account. Please check the ID and try again.';
        }

        if (str_contains($q, 'smtp') || str_contains($q, 'email') || str_contains($q, 'mail')) {
            return 'Email uses the '.config('mail.default').' mailer now. For real sending, set MAIL_MAILER=smtp, MAIL_HOST, MAIL_PORT, MAIL_SCHEME, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, and MAIL_FROM_NAME in .env, then clear config cache.';
        }

        if (str_contains($q, 'ai') || str_contains($q, 'assistant')) {
            $mode = filled(config('services.ai_assistant.api_key')) ? 'external AI is configured' : 'local fallback is active until an API key is added';
            return 'Village Link AI Assistant is available for customer, driver, and admin accounts. Current mode: '.$mode.'. It can answer using parcel, payment, driver, complaint, report, and setup context.';
        }

        if (str_contains($q, 'map') || str_contains($q, 'gps') || str_contains($q, 'route')) {
            return 'Tracking pages show a live route map with pickup, drop-off, and vehicle marker. Drivers can start live GPS from their delivery status page, and customers see updates automatically.';
        }

        if ($isAdmin && (str_contains($q, 'driver') || str_contains($q, 'free') || str_contains($q, 'available'))) {
            $available = User::whereIn('role', ['driver', 'agent'])->whereHas('driverProfile', fn ($query) => $query->where('availability_status', 'available'))->count();
            $busy = User::whereIn('role', ['driver', 'agent'])->whereHas('driverProfile', fn ($query) => $query->where('availability_status', 'busy'))->count();
            return 'Admin driver summary: '.$available.' available driver(s), '.$busy.' busy driver(s). The assign page blocks busy drivers from receiving another active delivery.';
        }

        if ($isAdmin && (str_contains($q, 'report') || str_contains($q, 'summary') || str_contains($q, 'system'))) {
            $active = Parcel::where('status', '!=', 'delivered')->count();
            $pendingPayments = Payment::where('status', 'pending')->count();
            $openComplaints = Complaint::whereIn('status', ['open', 'in_progress'])->count();
            return 'System summary: '.$active.' active parcel(s), '.$pendingPayments.' payment(s) pending verification, and '.$openComplaints.' active complaint(s). Open Reports for full CSV export.';
        }

        if ($isDriver && (str_contains($q, 'delivery') || str_contains($q, 'assigned') || str_contains($q, 'work'))) {
            $active = Parcel::where('agent_id', $user->id)->where('status', '!=', 'delivered')->count();
            $delivered = Parcel::where('agent_id', $user->id)->where('status', 'delivered')->count();
            return 'Your driver workspace has '.$active.' active assigned delivery/deliveries and '.$delivered.' delivered parcel(s). Open Deliveries to update status, share live GPS, and upload proof.';
        }

        if (str_contains($q, 'track')) {
            return 'Open Track Parcel or My Parcels and select the tracking ID. The page shows status history, route progress, ETA, driver name, and latest GPS update when available.';
        }

        if (str_contains($q, 'book')) {
            return 'Go to Book Parcel, enter sender and receiver details, choose delivery speed and weight, then continue to payment.';
        }

        if (str_contains($q, 'pay') || str_contains($q, 'payment')) {
            return 'Use Online Gateway when it is available. If it shows as unavailable, choose Bank Transfer or Cash. Bank and cash payments stay pending until admin verifies them.';
        }

        if (str_contains($q, 'complaint')) {
            $open = Complaint::where('user_id', $user->id)->whereIn('status', ['open', 'in_progress'])->count();
            return 'You have '.$open.' active complaint(s). Open Complaints to submit a new issue or view admin replies.';
        }

        return 'I can help with parcel tracking, booking, payments, driver assignment, live route map, delivery ETA, complaints, reports, email setup, and AI setup. Include a tracking ID for parcel-specific help.';
    }
}
