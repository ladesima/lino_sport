<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('bookings', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('bookings', 'approved_by')) $cols[] = 'approved_by';
            if (Schema::hasColumn('bookings', 'approved_at')) $cols[] = 'approved_at';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
