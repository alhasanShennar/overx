<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('earning_period_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('btc_earned', 20, 8);
            $table->decimal('btc_price', 20, 2);
            // revenue = btc_earned * btc_price (auto-calculated)
            $table->decimal('revenue', 20, 2);
            $table->text('additional_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
