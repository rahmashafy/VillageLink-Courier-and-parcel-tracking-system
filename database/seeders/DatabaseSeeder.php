<?php

namespace Database\Seeders;

use App\Models\Parcel;
use App\Models\ParcelStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::updateOrCreate(['email' => 'admin@villagelink.test'], [
            'name' => 'Village Link Admin',
            'phone' => '0770000001',
            'address' => 'Village Link Operations Center',
            'password' => $password,
            'role' => 'admin',
        ]);

        $customer = User::updateOrCreate(['email' => 'customer@villagelink.test'], [
            'name' => 'Nimal Perera',
            'phone' => '0771234567',
            'address' => 'No. 123, Main Street, Kurunegala',
            'password' => $password,
            'role' => 'customer',
        ]);

        $driver = User::updateOrCreate(['email' => 'driver@villagelink.test'], [
            'name' => 'Sampath Wickrama',
            'phone' => '0779876543',
            'address' => 'Kandy',
            'password' => $password,
            'role' => 'driver',
        ]);

        $driver->driverProfile()->updateOrCreate(['user_id' => $driver->id], [
            'phone' => '0779876543',
            'vehicle_type' => 'Van',
            'vehicle_number' => 'WP CAB-2045',
            'license_number' => 'DL-2045-7788',
            'availability_status' => 'busy',
            'shift_start' => '08:00',
            'shift_end' => '18:00',
            'current_lat' => 7.0128,
            'current_lng' => 80.0526,
            'last_location_at' => now()->subMinutes(8),
        ]);

        $activeParcel = Parcel::updateOrCreate(['tracking_id' => 'TRK2026070401'], [
            'user_id' => $customer->id,
            'agent_id' => $driver->id,
            'sender_name' => 'Nimal Perera',
            'receiver_name' => 'Kamal Silva',
            'receiver_phone' => '0772223344',
            'pickup_address' => 'No. 123, Main Street, Kurunegala',
            'delivery_address' => 'No. 45, DS Senanayake Street, Kandy',
            'parcel_type' => 'Documents',
            'delivery_type' => 'express',
            'weight' => 3,
            'length_cm' => 30,
            'width_cm' => 20,
            'height_cm' => 10,
            'volumetric_weight' => 1.2,
            'chargeable_weight' => 3,
            'distance_km' => 95,
            'original_price' => 1450,
            'loyalty_discount_amount' => 0,
            'price' => 1450,
            'status' => 'out_for_delivery',
            'payment_status' => 'paid',
            'pickup_location' => 'Kurunegala',
            'delivery_location' => 'Kandy',
            'current_lat' => 7.0128,
            'current_lng' => 80.0526,
            'estimated_delivery_at' => now()->addHours(3),
            'delivered_at' => null,
        ]);

        Payment::updateOrCreate(['parcel_id' => $activeParcel->id, 'reference' => 'DEMO-ACTIVE'], [
            'user_id' => $customer->id,
            'amount' => 1450,
            'currency' => 'LKR',
            'method' => 'cash',
            'provider' => 'manual',
            'status' => 'paid',
            'paid_at' => now()->subHour(),
        ]);

        foreach (['pending_pickup', 'picked_up', 'in_transit', 'arrived_center', 'out_for_delivery'] as $status) {
            ParcelStatus::firstOrCreate([
                'parcel_id' => $activeParcel->id,
                'status' => $status,
            ], [
                'user_id' => $driver->id,
                'note' => 'Demo delivery workflow update',
            ]);
        }

        $completedParcel = Parcel::updateOrCreate(['tracking_id' => 'TRK2026070301'], [
            'user_id' => $customer->id,
            'agent_id' => $driver->id,
            'sender_name' => 'Nimal Perera',
            'receiver_name' => 'Saman Perera',
            'receiver_phone' => '0775556677',
            'pickup_address' => 'Kurunegala Town',
            'delivery_address' => 'Colombo Fort',
            'parcel_type' => 'Small Parcel',
            'delivery_type' => 'standard',
            'weight' => 2,
            'length_cm' => 25,
            'width_cm' => 18,
            'height_cm' => 12,
            'volumetric_weight' => 1.08,
            'chargeable_weight' => 2,
            'distance_km' => 120,
            'original_price' => 1250,
            'loyalty_discount_amount' => 0,
            'price' => 1250,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'pickup_location' => 'Kurunegala',
            'delivery_location' => 'Colombo',
            'current_lat' => 6.9271,
            'current_lng' => 79.8612,
            'estimated_delivery_at' => now()->subHours(2),
            'delivered_at' => now()->subHours(3),
            'delivery_notes' => 'Handed over to receiver.',
        ]);

        Payment::updateOrCreate(['parcel_id' => $completedParcel->id, 'reference' => 'DEMO-COMPLETE'], [
            'user_id' => $customer->id,
            'amount' => 1250,
            'currency' => 'LKR',
            'method' => 'card',
            'provider' => 'manual',
            'status' => 'paid',
            'paid_at' => now()->subHours(4),
        ]);

        ParcelStatus::firstOrCreate([
            'parcel_id' => $completedParcel->id,
            'status' => 'delivered',
        ], [
            'user_id' => $driver->id,
            'note' => 'Delivered successfully.',
        ]);
    }
}
