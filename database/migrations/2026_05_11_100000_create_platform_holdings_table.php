<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_holdings', function (Blueprint $table) {
            $table->id();
            $table->decimal('btc_unit', 20, 8)->default(0);
            $table->decimal('btc_value', 20, 2)->default(0);
            $table->decimal('eth_unit', 20, 8)->default(0);
            $table->decimal('eth_value', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_holdings');
    }
};
