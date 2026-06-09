<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('cashout_approval_level')->nullable()->after('password');
        });

        Schema::table('cashouts', function (Blueprint $table) {
            $table->foreignId('approved_1_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_1_at')->nullable()->after('approved_1_by');
            $table->foreignId('approved_2_by')->nullable()->after('approved_1_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_2_at')->nullable()->after('approved_2_by');
            $table->foreignId('approved_3_by')->nullable()->after('approved_2_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_3_at')->nullable()->after('approved_3_by');
        });
    }

    public function down(): void
    {
        Schema::table('cashouts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_1_by');
            $table->dropColumn('approved_1_at');
            $table->dropConstrainedForeignId('approved_2_by');
            $table->dropColumn('approved_2_at');
            $table->dropConstrainedForeignId('approved_3_by');
            $table->dropColumn('approved_3_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cashout_approval_level');
        });
    }
};
