<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'provider')) {
                    $table->string('provider')->default('manual')->after('method');
                }
                if (! Schema::hasColumn('payments', 'currency')) {
                    $table->string('currency', 3)->default('LKR')->after('amount');
                }
                if (! Schema::hasColumn('payments', 'reference')) {
                    $table->string('reference')->nullable()->index()->after('provider');
                }
                if (! Schema::hasColumn('payments', 'gateway_order_id')) {
                    $table->string('gateway_order_id')->nullable()->index()->after('reference');
                }
                if (! Schema::hasColumn('payments', 'gateway_payment_id')) {
                    $table->string('gateway_payment_id')->nullable()->after('gateway_order_id');
                }
                if (! Schema::hasColumn('payments', 'gateway_status')) {
                    $table->string('gateway_status')->nullable()->after('gateway_payment_id');
                }
                if (! Schema::hasColumn('payments', 'checkout_payload')) {
                    $table->json('checkout_payload')->nullable()->after('payment_details');
                }
                if (! Schema::hasColumn('payments', 'paid_at')) {
                    $table->timestamp('paid_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('payments', 'failed_at')) {
                    $table->timestamp('failed_at')->nullable()->after('paid_at');
                }
                if (! Schema::hasColumn('payments', 'failure_reason')) {
                    $table->text('failure_reason')->nullable()->after('failed_at');
                }
            });
        }

        if (Schema::hasTable('parcels')) {
            Schema::table('parcels', function (Blueprint $table) {
                if (! Schema::hasColumn('parcels', 'current_lat')) {
                    $table->decimal('current_lat', 10, 7)->nullable()->after('delivery_location');
                }
                if (! Schema::hasColumn('parcels', 'current_lng')) {
                    $table->decimal('current_lng', 10, 7)->nullable()->after('current_lat');
                }
                if (! Schema::hasColumn('parcels', 'delivery_proof_path')) {
                    $table->string('delivery_proof_path')->nullable()->after('current_lng');
                }
                if (! Schema::hasColumn('parcels', 'delivery_notes')) {
                    $table->text('delivery_notes')->nullable()->after('delivery_proof_path');
                }
                if (! Schema::hasColumn('parcels', 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('estimated_delivery_at');
                }
            });
        }

        if (! Schema::hasTable('driver_profiles')) {
            Schema::create('driver_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('phone')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->string('vehicle_number')->nullable();
                $table->string('license_number')->nullable();
                $table->string('availability_status')->default('available');
                $table->time('shift_start')->nullable();
                $table->time('shift_end')->nullable();
                $table->decimal('current_lat', 10, 7)->nullable();
                $table->decimal('current_lng', 10, 7)->nullable();
                $table->timestamp('last_location_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('parcel_locations')) {
            Schema::create('parcel_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parcel_id')->constrained()->cascadeOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->decimal('speed_kmh', 6, 2)->nullable();
                $table->string('note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('complaint_replies')) {
            Schema::create('complaint_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('message');
                $table->boolean('is_admin')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_replies');
        Schema::dropIfExists('parcel_locations');
        Schema::dropIfExists('driver_profiles');

        if (Schema::hasTable('parcels')) {
            Schema::table('parcels', function (Blueprint $table) {
                foreach (['current_lat', 'current_lng', 'delivery_proof_path', 'delivery_notes', 'delivered_at'] as $column) {
                    if (Schema::hasColumn('parcels', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                foreach (['provider', 'currency', 'reference', 'gateway_order_id', 'gateway_payment_id', 'gateway_status', 'checkout_payload', 'paid_at', 'failed_at', 'failure_reason'] as $column) {
                    if (Schema::hasColumn('payments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
