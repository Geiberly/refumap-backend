<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citizen_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('report_type', [
                'new_help_point',
                'shelter_full',
                'hospital_closed',
                'road_blocked',
                'danger_zone',
                'lack_of_supplies',
                'collapsed_building',
                'incorrect_info',
                'other',
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('contact_phone', 30)->nullable();

            $table->enum('status', ['pending', 'verified', 'rejected', 'converted'])
                  ->default('pending');

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('converted_map_point_id')->nullable()->constrained('map_points')->nullOnDelete();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('report_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizen_reports');
    }
};
