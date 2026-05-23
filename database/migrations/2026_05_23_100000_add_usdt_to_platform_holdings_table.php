<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_holdings', function (Blueprint $table) {
            $table->decimal('usdt_value', 20, 2)->default(0)->after('eth_value');
        });
    }

    public function down(): void
    {
        Schema::table('platform_holdings', function (Blueprint $table) {
            $table->dropColumn(['usdt_value']);
        });
    }
};
