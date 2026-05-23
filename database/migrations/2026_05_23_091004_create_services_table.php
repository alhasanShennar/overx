<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('order')->default(0);
            $table->string('number', 10);               // e.g. "02"
            $table->string('title');
            $table->string('tagline')->nullable();

            // Overview section
            $table->string('overview_title')->nullable();
            $table->text('overview_description')->nullable();
            $table->string('overview_image')->nullable();

            // Process section
            $table->string('process_title')->nullable();
            $table->text('process_description')->nullable();
            $table->json('steps')->nullable();           // [{icon, title, description}]

            // FAQs
            $table->json('faqs')->nullable();            // [{question, answer}]

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
