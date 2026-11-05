<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('court_id')->constrained()->onDelete('cascade');
            
            // Biodata Lengkap Pemesan (Guest Booking Aman)
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->string('customer_nik')->nullable(); // NIK / No. KTP / Identitas
            
            $table->dateTime('start_dt');
            $table->dateTime('end_dt');
            $table->json('equipment_json')->nullable();
            $table->integer('total_price');
            $table->integer('dp_amount')->default(0);
            $table->string('snap_token')->nullable();
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed, expired
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['dp', 'lunas']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('bookings'); }
};
