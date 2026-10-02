<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        Schema::create('parcels', function (Blueprint $table) {
    $table->id();
    $table->string('tracking_id')-> unique();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('sender_name');
    $table->string('receiver_name');
    $table->string('receiver_phone');
    $table->text('pickup_address');
    $table->text('delivery_address');
    $table->string('parcel_type');
    $table->decimal('weight', 8, 2);
    $table->decimal('price', 10, 2)->default(0);
    $table->string('status')->default('pending_pickup');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcels');
    }
};
