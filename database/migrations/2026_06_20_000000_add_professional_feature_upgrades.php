<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'theme_preference')) {
                    $table->string('theme_preference')->default('dark');
                }
                if (! Schema::hasColumn('users', 'loyalty_points')) {
                    $table->unsignedInteger('loyalty_points')->default(0);
                }
            });
        }

        if (Schema::hasTable('parcels')) {
            Schema::table('parcels', function (Blueprint $table) {
                if (! Schema::hasColumn('parcels', 'length_cm')) {
                    $table->decimal('length_cm', 8, 2)->nullable();
                }
                if (! Schema::hasColumn('parcels', 'width_cm')) {
                    $table->decimal('width_cm', 8, 2)->nullable();
                }
                if (! Schema::hasColumn('parcels', 'height_cm')) {
                    $table->decimal('height_cm', 8, 2)->nullable();
                }
                if (! Schema::hasColumn('parcels', 'volumetric_weight')) {
                    $table->decimal('volumetric_weight', 8, 2)->default(0);
                }
                if (! Schema::hasColumn('parcels', 'chargeable_weight')) {
                    $table->decimal('chargeable_weight', 8, 2)->default(0);
                }
                if (! Schema::hasColumn('parcels', 'original_price')) {
                    $table->decimal('original_price', 10, 2)->nullable();
                }
                if (! Schema::hasColumn('parcels', 'loyalty_discount_amount')) {
                    $table->decimal('loyalty_discount_amount', 10, 2)->default(0);
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'loyalty_points_redeemed')) {
                    $table->unsignedInteger('loyalty_points_redeemed')->default(0);
                }
                if (! Schema::hasColumn('payments', 'loyalty_discount')) {
                    $table->decimal('loyalty_discount', 10, 2)->default(0);
                }
                if (! Schema::hasColumn('payments', 'loyalty_points_awarded')) {
                    $table->unsignedInteger('loyalty_points_awarded')->default(0);
                }
            });
        }

        if (! Schema::hasTable('loyalty_transactions')) {
            Schema::create('loyalty_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('parcel_id')->nullable()->constrained()->nullOnDelete();
                $table->integer('points');
                $table->string('type');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('refund_requests')) {
            Schema::create('refund_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('parcel_id')->constrained()->cascadeOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type')->default('refund');
                $table->string('status')->default('pending');
                $table->decimal('requested_amount', 10, 2)->default(0);
                $table->decimal('approved_amount', 10, 2)->nullable();
                $table->text('reason');
                $table->text('admin_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
        Schema::dropIfExists('loyalty_transactions');

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                foreach (['loyalty_points_redeemed', 'loyalty_discount', 'loyalty_points_awarded'] as $column) {
                    if (Schema::hasColumn('payments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('parcels')) {
            Schema::table('parcels', function (Blueprint $table) {
                foreach (['length_cm', 'width_cm', 'height_cm', 'volumetric_weight', 'chargeable_weight', 'original_price', 'loyalty_discount_amount'] as $column) {
                    if (Schema::hasColumn('parcels', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['theme_preference', 'loyalty_points'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
