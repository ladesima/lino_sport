<?php

namespace App\Providers;

use App\Models\Rate;
use App\Models\Equipment;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (Schema::hasTable('bookings')) {
                if (!Schema::hasColumn('bookings', 'approved_by')) {
                    Schema::table('bookings', function (Blueprint $table) {
                        $table->unsignedBigInteger('approved_by')->nullable()->after('user_id');
                        $table->timestamp('approved_at')->nullable()->after('status');
                    });
                }

                if (!Schema::hasColumn('bookings', 'payment_proof')) {
                    Schema::table('bookings', function (Blueprint $table) {
                        $table->string('payment_proof')->nullable()->after('payment_method');
                        $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof');
                    });
                }
            }

            if (Schema::hasTable('rates')) {
                if (DB::getDriverName() === 'mysql') {
                    @DB::statement("ALTER TABLE `rates` MODIFY `sport` VARCHAR(50) NOT NULL");
                    @DB::statement("ALTER TABLE `rates` MODIFY `day_type` VARCHAR(50) NOT NULL");
                }

                $hasGymRate = Rate::where('sport', 'gym')->exists();
                if (!$hasGymRate) {
                    Rate::syncStandardRates();
                }

                $hasNewRate = Rate::where('day_type', 'weekday_day')->exists();
                if (!$hasNewRate) {
                    Rate::syncStandardRates();

                    if (Schema::hasTable('equipment')) {
                        Equipment::query()->delete();
                        $equipments = [
                            ['name' => 'Sewa Raket Padel (/raket)', 'price' => 25000],
                            ['name' => 'Sewa Bola Padel 1 Tube (3 bola)', 'price' => 120000],
                            ['name' => 'Sewa Bulu Badminton (/pcs)', 'price' => 15000],
                            ['name' => 'Sewa Raket + Bola Pickleball (/raket)', 'price' => 15000],
                        ];
                        foreach ($equipments as $eq) {
                            Equipment::create($eq);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika belum terkoneksi database
        }
    }
}
