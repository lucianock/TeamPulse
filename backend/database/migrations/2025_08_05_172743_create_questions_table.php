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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->string('question_text');
            $table->enum('type', ['text', 'textarea', 'rating_1_5', 'rating_1_10', 'multiple_choice', 'single_choice', 'yes_no']);
            $table->json('options')->nullable(); // Para preguntas de opción múltiple
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->text('help_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
