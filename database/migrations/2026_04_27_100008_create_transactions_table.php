<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('earning_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['store', 'cashout', 'adjustment']);
            $table->decimal('btc_amount', 20, 8)->default(0);
            $table->decimal('fiat_amount', 20, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'cancelled', 'rejected'])->default('pending');
            $table->enum('requested_by', ['client', 'admin'])->default('client');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
