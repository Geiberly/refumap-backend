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
        Schema::create('hospital_needs_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('map_points')->cascadeOnDelete();
            $table->string('hospital_name')->nullable();
            $table->text('needs'); // Array/JSON or comma separated
            $table->text('description')->nullable();
            $table->string('source')->default('citizen');
            $table->string('reporter_name')->nullable();
            $table->string('reporter_contact')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_needs_reports');
    }
};
