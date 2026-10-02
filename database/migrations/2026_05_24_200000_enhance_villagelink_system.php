<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
        });

        Schema::table('parcels', function (Blueprint $table) {
            $table->string('delivery_type')->default('standard')->after('parcel_type');
            $table->decimal('distance_km', 8, 2)->default(50)->after('weight');
            $table->string('pickup_location')->nullable()->after('pickup_address');
            $table->string('delivery_location')->nullable()->after('delivery_address');
            $table->timestamp('estimated_delivery_at')->nullable()->after('status');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->unsignedTinyInteger('speed_rating')->nullable()->after('rating');
            $table->unsignedTinyInteger('behavior_rating')->nullable()->after('speed_rating');
            $table->unsignedTinyInteger('safety_rating')->nullable()->after('behavior_rating');
            $table->unsignedTinyInteger('service_rating')->nullable()->after('safety_rating');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->string('category')->default('general')->after('parcel_id');
            $table->string('image_path')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address']);
        });

        Schema::table('parcels', function (Blueprint $table) {
            $table->dropColumn(['delivery_type', 'distance_km', 'pickup_location', 'delivery_location', 'estimated_delivery_at']);
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropColumn(['speed_rating', 'behavior_rating', 'safety_rating', 'service_rating']);
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['category', 'image_path']);
        });
    }
};
