<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('bookings', 'payment_proof_uploaded_at')) {
                $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('bookings', 'payment_proof')) $cols[] = 'payment_proof';
            if (Schema::hasColumn('bookings', 'payment_proof_uploaded_at')) $cols[] = 'payment_proof_uploaded_at';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
