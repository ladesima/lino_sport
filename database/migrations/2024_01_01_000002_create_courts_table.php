<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('sport', ['padel', 'bulutangkis', 'pickleball']);
            $table->string('image')->nullable();
            $table->time('opening_time')->default('07:00:00');
            $table->time('closing_time')->default('23:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('courts'); }
};
