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
        Schema::create('admitted_people', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('alias')->nullable();
            $table->string('approx_age')->nullable();
            $table->string('sex')->nullable();
            $table->foreignId('hospital_id')->nullable()->constrained('map_points')->nullOnDelete();
            $table->string('hospital_name_snapshot')->nullable();
            
            // Strictly without 'fallecida'
            $table->enum('status_general', [
                'ingresada', 
                'en_observacion', 
                'trasladada', 
                'dada_de_alta', 
                'sin_identificar'
            ])->default('ingresada');
            
            $table->dateTime('admitted_at')->nullable();
            $table->text('public_notes')->nullable();
            $table->string('source')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_contact')->nullable();
            
            // Visibility status controlled by operators
            $table->enum('visibility_status', [
                'active', 
                'hidden', 
                'duplicate', 
                'removed'
            ])->default('active');
            
            $table->foreignId('hidden_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hidden_at')->nullable();
            $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('removed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admitted_people');
    }
};
