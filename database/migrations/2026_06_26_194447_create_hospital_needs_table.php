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
        Schema::create('hospital_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('map_points')->cascadeOnDelete();
            $table->string('need_type');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('quantity')->nullable();
            $table->string('status')->default('active'); // active, fulfilled
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_needs');
    }
};
