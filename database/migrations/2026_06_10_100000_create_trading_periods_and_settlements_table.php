<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_contract_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_earning', 20, 2)->default(0);
            $table->enum('status', [
                'pending',
                'completed',
                'request_pending',
                'stored',
                'cashed_out',
                'rejected',
            ])->default('pending');
            $table->enum('client_decision', ['cashout', 'store'])->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['trading_contract_id', 'year', 'month']);
        });

        Schema::table('trading_earnings', function (Blueprint $table) {
            $table->foreignId('trading_period_id')
                ->nullable()
                ->after('trading_contract_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::create('trading_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_period_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['cashout', 'store']);
            $table->decimal('amount', 20, 2);
            $table->enum('status', ['pending', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->string('requested_by')->default('client');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('trading_cashouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashout_details_id')->nullable()->constrained('cashout_details')->nullOnDelete();
            $table->decimal('amount', 20, 2);
            $table->string('receipt')->nullable();
            $table->date('date')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('trading_stored_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_contract_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 2);
            $table->timestamp('stored_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_stored_earnings');
        Schema::dropIfExists('trading_cashouts');
        Schema::dropIfExists('trading_transactions');

        Schema::table('trading_earnings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trading_period_id');
        });

        Schema::dropIfExists('trading_periods');
    }
};
