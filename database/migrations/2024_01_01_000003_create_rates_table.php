<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->string('sport', 50);
            $table->string('day_type', 50);
            $table->integer('price');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('rates'); }
};
