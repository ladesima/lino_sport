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
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('paid_amount')->default(0)->after('dp_amount');
            $table->string('settlement_token')->nullable()->after('snap_token');
            $table->string('settlement_status')->default('unpaid')->after('payment_status'); // unpaid, paid
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'settlement_token', 'settlement_status']);
        });
    }
};
