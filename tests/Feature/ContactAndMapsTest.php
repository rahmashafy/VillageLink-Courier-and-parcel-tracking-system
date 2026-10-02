<?php

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

test('contact form stores message and sends inbox email', function () {
    Mail::fake();

    $response = $this->post(route('contact.store'), [
        'name' => 'Nimal Perera',
        'email' => 'nimal@example.com',
        'message' => 'I need help tracking my parcel.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $message = ContactMessage::first();

    expect($message)->not->toBeNull()
        ->and($message->name)->toBe('Nimal Perera')
        ->and($message->email)->toBe('nimal@example.com');

    Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) use ($message) {
        return $mail->hasTo(config('services.contact.inbox'))
            && $mail->contactMessage->is($message);
    });
});

test('customer route preview returns driving route for any city pair', function () {
    config([
        'services.maps.api_key' => 'test-google-key',
        'services.maps.region' => 'lk',
    ]);

    Http::fake([
        'maps.googleapis.com/maps/api/geocode/json*' => Http::sequence()
            ->push([
                'results' => [['geometry' => ['location' => ['lat' => 6.9271, 'lng' => 79.8612]]]],
            ])
            ->push([
                'results' => [['geometry' => ['location' => ['lat' => 7.2906, 'lng' => 80.6337]]]],
            ]),
        'maps.googleapis.com/maps/api/directions/json*' => Http::response([
            'routes' => [[
                'overview_polyline' => ['points' => '_p~iF~ps|U_ulLnnqC_mqNvxq`@'],
                'legs' => [[
                    'distance' => ['value' => 115000],
                    'duration' => ['value' => 7200],
                ]],
            ]],
        ]),
    ]);

    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)
        ->postJson(route('customer.parcels.route-preview'), [
            'pickup_location' => 'Colombo Fort',
            'delivery_location' => 'Kandy City',
        ]);

    $response->assertOk()
        ->assertJsonPath('pickup.lat', 6.9271)
        ->assertJsonPath('delivery.lng', 80.6337)
        ->assertJsonPath('distance_km', 115);

    expect(count($response->json('route_points')))->toBeGreaterThanOrEqual(2);
});

test('route preview supports new booking towns and common spelling mistakes without map keys', function () {
    config([
        'services.maps.api_key' => null,
        'services.maps.osrm_enabled' => false,
    ]);

    $customer = User::factory()->create(['role' => 'customer']);

    $mawanellaToDigana = $this->actingAs($customer)
        ->postJson(route('customer.parcels.route-preview'), [
            'pickup_location' => 'mawanella',
            'delivery_location' => 'digana',
        ]);

    $mawanellaToDigana->assertOk();
    expect(count($mawanellaToDigana->json('route_points')))->toBeGreaterThanOrEqual(2)
        ->and((float) $mawanellaToDigana->json('distance_km'))->toBeGreaterThan(1);

    $kurunegalaToTypo = $this->actingAs($customer)
        ->postJson(route('customer.parcels.route-preview'), [
            'pickup_location' => 'kurunegala',
            'delivery_location' => 'apuradhapura',
        ]);

    $kurunegalaToTypo->assertOk();
    expect(count($kurunegalaToTypo->json('route_points')))->toBeGreaterThanOrEqual(2)
        ->and((float) $kurunegalaToTypo->json('distance_km'))->toBeGreaterThan(1);
});

test('route preview geocodes sri lankan places outside local city list with openstreetmap fallback', function () {
    config([
        'services.maps.api_key' => null,
        'services.maps.osrm_enabled' => false,
        'services.maps.nominatim_enabled' => true,
    ]);

    Http::fake([
        'nominatim.openstreetmap.org/search*' => Http::sequence()
            ->push([[
                'lat' => '7.123456',
                'lon' => '80.123456',
                'display_name' => 'Unknown Test Town Alpha, Sri Lanka',
            ]])
            ->push([[
                'lat' => '8.234567',
                'lon' => '81.234567',
                'display_name' => 'Unknown Test Town Beta, Sri Lanka',
            ]]),
    ]);

    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)
        ->postJson(route('customer.parcels.route-preview'), [
            'pickup_location' => 'Unknown Test Town Alpha',
            'delivery_location' => 'Unknown Test Town Beta',
        ]);

    $response->assertOk()
        ->assertJsonPath('pickup.lat', 7.123456)
        ->assertJsonPath('delivery.lng', 81.234567);

    expect(count($response->json('route_points')))->toBeGreaterThanOrEqual(2);
});
