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
        Schema::table('surveys', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['created_by']);
            
            // Drop the columns
            $table->dropColumn(['organization_id', 'team_id', 'created_by']);
            
            // Add new columns for SaaS model
            $table->string('creator_name')->nullable()->after('max_responses');
            $table->string('creator_email')->nullable()->after('creator_name');
        });
        
        Schema::table('survey_responses', function (Blueprint $table) {
            // Drop user_id foreign key and column
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // Remove SaaS columns
            $table->dropColumn(['creator_name', 'creator_email']);
            
            // Add back original columns
            $table->foreignId('organization_id')->constrained()->after('id');
            $table->foreignId('team_id')->nullable()->constrained()->after('organization_id');
            $table->foreignId('created_by')->constrained('users')->after('team_id');
        });
        
        Schema::table('survey_responses', function (Blueprint $table) {
            // Add back user_id
            $table->foreignId('user_id')->nullable()->constrained()->after('question_id');
        });
    }
};
