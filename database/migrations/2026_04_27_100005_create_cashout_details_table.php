<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashout_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->enum('type', ['crypto', 'bank'])->default('crypto');
            // Crypto fields
            $table->string('crypto_wallet_address')->nullable();
            $table->string('crypto_network')->nullable();
            // Bank fields
            $table->string('account_holder')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('iban')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashout_details');
    }
};
