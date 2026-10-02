<?php

use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Parcel;
use App\Models\Payment;
use App\Models\User;

function makeParcel(array $overrides = []): Parcel
{
    return Parcel::create(array_merge([
        'tracking_id' => 'TRK'.random_int(100000, 999999),
        'user_id' => User::factory()->create(['role' => 'customer'])->id,
        'sender_name' => 'Sender',
        'receiver_name' => 'Receiver',
        'receiver_phone' => '0771234567',
        'pickup_address' => 'Colombo',
        'delivery_address' => 'Kandy',
        'pickup_location' => 'Colombo',
        'delivery_location' => 'Kandy',
        'parcel_type' => 'Document',
        'delivery_type' => 'standard',
        'weight' => 2,
        'distance_km' => 115,
        'price' => 1175,
        'status' => 'pending_pickup',
        'payment_status' => 'unpaid',
    ], $overrides));
}

test('admin cannot assign a busy driver to another active parcel', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $driver = User::factory()->create(['role' => 'driver']);
    $activeParcel = makeParcel(['agent_id' => $driver->id, 'status' => 'in_transit']);
    $newParcel = makeParcel(['status' => 'pending_pickup']);

    $response = $this->actingAs($admin)
        ->post(route('admin.parcels.assign.store', $newParcel), [
            'driver_id' => $driver->id,
        ]);

    $response->assertSessionHasErrors('driver_id');
    expect($newParcel->refresh()->agent_id)->toBeNull();
    expect($activeParcel->refresh()->agent_id)->toBe($driver->id);
});

test('customer ai assistant answers with parcel data', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $parcel = makeParcel([
        'user_id' => $customer->id,
        'tracking_id' => 'TRK2026060801',
        'status' => 'out_for_delivery',
    ]);

    $response = $this->actingAs($customer)
        ->postJson(route('customer.chat.ask'), [
            'message' => 'Where is TRK2026060801?',
        ]);

    $response->assertOk()
        ->assertJsonPath('reply', fn (string $reply) => str_contains($reply, $parcel->tracking_id) && str_contains($reply, 'out for delivery'));
});

test('ai assistant route is available to admin users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    makeParcel(['status' => 'in_transit']);

    $response = $this->actingAs($admin)
        ->postJson(route('assistant.ask'), [
            'message' => 'Give system summary',
        ]);

    $response->assertOk()
        ->assertJsonPath('reply', fn (string $reply) => str_contains($reply, 'System summary'));
});

test('customer live location endpoint returns route points', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $parcel = makeParcel([
        'user_id' => $customer->id,
        'pickup_location' => 'Colombo',
        'delivery_location' => 'Kandy',
        'current_lat' => 7.1001,
        'current_lng' => 80.2002,
    ]);

    $response = $this->actingAs($customer)
        ->getJson(route('customer.parcels.location', $parcel));

    $response->assertOk()
        ->assertJsonPath('pickup.lat', 6.9271)
        ->assertJsonPath('delivery.lng', 80.6337)
        ->assertJsonPath('vehicle.lat', 7.1001);

    expect(count($response->json('route_points')))->toBeGreaterThanOrEqual(2);
});

test('driver can send live gps updates for assigned parcel', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $parcel = makeParcel([
        'agent_id' => $driver->id,
        'status' => 'in_transit',
    ]);

    $response = $this->actingAs($driver)
        ->postJson(route('agent.parcels.location.update', $parcel), [
            'latitude' => 7.2906,
            'longitude' => 80.6337,
            'note' => 'Live driver GPS',
        ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Location updated.');

    expect((float) $parcel->refresh()->current_lat)->toBe(7.2906);
    expect($driver->driverProfile()->exists())->toBeTrue();
});

test('complaint conversations support admin replies', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $complaint = Complaint::create([
        'user_id' => $customer->id,
        'subject' => 'Late parcel',
        'message' => 'Parcel is late',
        'category' => 'late_delivery',
        'status' => 'open',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.complaints.reply', $complaint), [
            'message' => 'We are checking with the driver.',
            'status' => 'in_progress',
        ])
        ->assertSessionHasNoErrors();

    expect(ComplaintReply::where('complaint_id', $complaint->id)->count())->toBe(1);
    expect($complaint->refresh()->status)->toBe('in_progress');
});

test('admin can confirm manual payment and mark parcel paid', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $parcel = makeParcel(['user_id' => $customer->id]);

    $this->actingAs($customer)
        ->post(route('customer.payments.store', $parcel), [
            'method' => 'bank_transfer',
            'bank_name' => 'Test Bank',
            'transfer_reference' => 'BANK123',
        ])
        ->assertSessionHasNoErrors();

    $payment = Payment::firstOrFail();
    expect($payment->status)->toBe('pending');

    $this->actingAs($admin)
        ->patch(route('admin.payments.update', $payment), [
            'status' => 'paid',
        ])
        ->assertSessionHasNoErrors();

    expect($payment->refresh()->status)->toBe('paid');
    expect($parcel->refresh()->payment_status)->toBe('paid');
});

test('parcel booking uses volumetric chargeable weight', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->post(route('customer.parcels.store'), [
            'sender_name' => 'Sender',
            'receiver_name' => 'Receiver',
            'receiver_phone' => '0771234567',
            'pickup_address' => 'Colombo address',
            'delivery_address' => 'Kandy address',
            'pickup_location' => 'Colombo',
            'delivery_location' => 'Kandy',
            'parcel_type' => 'package',
            'delivery_type' => 'standard',
            'weight' => 1,
            'length_cm' => 50,
            'width_cm' => 40,
            'height_cm' => 30,
        ])
        ->assertRedirect();

    $parcel = Parcel::where('user_id', $customer->id)->firstOrFail();

    expect((float) $parcel->volumetric_weight)->toBe(12.0);
    expect((float) $parcel->chargeable_weight)->toBe(12.0);
    expect((float) $parcel->price)->toBeGreaterThan(2000);
});

test('new booked parcel appears on admin dashboard and assigned driver dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $driver = User::factory()->create(['role' => 'driver']);

    $this->actingAs($customer)
        ->post(route('customer.parcels.store'), [
            'sender_name' => 'Nimal Perera',
            'receiver_name' => 'Saman Perera',
            'receiver_phone' => '0775556677',
            'pickup_address' => 'Kurunegala Town',
            'delivery_address' => 'Colombo Fort',
            'pickup_location' => 'Kurunegala',
            'delivery_location' => 'Colombo',
            'parcel_type' => 'package',
            'delivery_type' => 'standard',
            'weight' => 3,
            'length_cm' => 30,
            'width_cm' => 20,
            'height_cm' => 15,
        ])
        ->assertRedirect();

    $parcel = Parcel::where('user_id', $customer->id)->latest()->firstOrFail();
    $this->flushSession();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee($parcel->tracking_id)
        ->assertSee('Saman Perera');

    $this->actingAs($driver)
        ->get(route('agent.dashboard'))
        ->assertOk()
        ->assertDontSee($parcel->tracking_id);

    $this->actingAs($admin)
        ->post(route('admin.parcels.assign.store', $parcel), [
            'driver_id' => $driver->id,
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($driver)
        ->get(route('agent.dashboard'))
        ->assertOk()
        ->assertSee($parcel->tracking_id)
        ->assertSee('Saman Perera')
        ->assertSee('Kurunegala')
        ->assertSee('Colombo');
});

test('driver deliveries page shows only parcels assigned to logged in driver', function () {
    $driver = User::factory()->create(['role' => 'driver', 'name' => 'Sampath Wickrama']);
    $otherDriver = User::factory()->create(['role' => 'driver', 'name' => 'Hasan Driver']);
    $ownParcel = makeParcel([
        'agent_id' => $driver->id,
        'tracking_id' => 'TRKOWNDRIVER01',
        'receiver_name' => 'Own Receiver',
        'status' => 'pending_pickup',
    ]);
    $otherParcel = makeParcel([
        'agent_id' => $otherDriver->id,
        'tracking_id' => 'TRKOTHERDRV01',
        'receiver_name' => 'Other Receiver',
        'status' => 'pending_pickup',
    ]);

    $this->actingAs($driver)
        ->get(route('agent.parcels.index'))
        ->assertOk()
        ->assertSee('Sampath Wickrama')
        ->assertSee($ownParcel->tracking_id)
        ->assertSee('Own Receiver')
        ->assertDontSee($otherParcel->tracking_id)
        ->assertDontSee('Other Receiver')
        ->assertSee('Only admin-assigned parcels show here.');
});

test('driver status page has fallback route gps for new booking towns', function () {
    config([
        'services.maps.api_key' => null,
        'services.maps.osrm_enabled' => false,
    ]);

    $driver = User::factory()->create(['role' => 'driver']);
    $parcel = makeParcel([
        'agent_id' => $driver->id,
        'pickup_location' => 'mawanella',
        'delivery_location' => 'digana',
        'pickup_address' => 'Mawanella',
        'delivery_address' => 'Digana',
        'status' => 'pending_pickup',
    ]);

    $this->actingAs($driver)
        ->get(route('agent.parcels.status', $parcel))
        ->assertOk()
        ->assertSee('Follow Route GPS')
        ->assertSee('Live GPS Running - Stop')
        ->assertSee('7.2536');
});

test('loyalty points can be redeemed and earned on verified payment', function () {
    $customer = User::factory()->create(['role' => 'customer', 'loyalty_points' => 250]);
    $admin = User::factory()->create(['role' => 'admin']);
    $parcel = makeParcel(['user_id' => $customer->id, 'price' => 1000]);

    $this->actingAs($customer)
        ->post(route('customer.payments.store', $parcel), [
            'method' => 'bank_transfer',
            'bank_name' => 'Test Bank',
            'transfer_reference' => 'LOYAL123',
            'use_loyalty' => 1,
        ])
        ->assertSessionHasNoErrors();

    $payment = Payment::firstOrFail();
    expect((float) $payment->amount)->toBe(800.0);
    expect($payment->loyalty_points_redeemed)->toBe(200);
    expect($customer->refresh()->loyalty_points)->toBe(50);

    $this->actingAs($admin)
        ->patch(route('admin.payments.update', $payment), ['status' => 'paid'])
        ->assertSessionHasNoErrors();

    expect($payment->refresh()->loyalty_points_awarded)->toBe(8);
    expect($customer->refresh()->loyalty_points)->toBe(58);
});

test('customer can request refund and admin can approve it', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $parcel = makeParcel(['user_id' => $customer->id, 'payment_status' => 'paid']);
    $payment = Payment::create([
        'parcel_id' => $parcel->id,
        'user_id' => $customer->id,
        'amount' => 1175,
        'currency' => 'LKR',
        'method' => 'bank_transfer',
        'provider' => 'manual',
        'reference' => 'REF123',
        'status' => 'paid',
    ]);

    $this->actingAs($customer)
        ->post(route('customer.refunds.store', $parcel), [
            'type' => 'refund',
            'reason' => 'I need to cancel this paid shipment.',
        ])
        ->assertSessionHasNoErrors();

    $request = \App\Models\RefundRequest::firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.refunds.update', $request), [
            'status' => 'approved',
            'approved_amount' => 1000,
            'admin_notes' => 'Approved for demo.',
        ])
        ->assertSessionHasNoErrors();

    expect($request->refresh()->status)->toBe('approved');
    expect($payment->refresh()->status)->toBe('refunded');
    expect($parcel->refresh()->payment_status)->toBe('refunded');
});

test('theme switch stores user preference', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->post(route('theme.switch', 'light'))
        ->assertRedirect();

    expect($customer->refresh()->theme_preference)->toBe('light');
});
