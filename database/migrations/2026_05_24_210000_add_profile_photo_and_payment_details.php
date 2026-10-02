<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('address');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->json('payment_details')->nullable()->after('method');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_details');
        });
    }
};
