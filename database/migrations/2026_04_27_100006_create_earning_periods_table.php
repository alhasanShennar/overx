<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earning_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_btc_earned', 20, 8)->default(0);
            $table->decimal('average_btc_price', 20, 2)->default(0);
            $table->decimal('total_revenue', 20, 2)->default(0);
            // pending: active period collecting earnings
            // completed: 30 days done, awaiting client decision
            // request_pending: client submitted cashout/store request, admin to process
            // stored: period earnings stored
            // cashed_out: period earnings cashed out
            // rejected: admin rejected client request
            $table->enum('status', ['pending', 'completed', 'request_pending', 'stored', 'cashed_out', 'rejected'])
                ->default('pending');
            $table->enum('client_decision', ['store', 'cashout'])->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_periods');
    }
};
