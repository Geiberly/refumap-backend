<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Estado del punto
            $table->enum('status', ['active', 'full', 'closed', 'unverified', 'danger', 'verified', 'pending_review'])
                  ->default('unverified');

            // Fuente de la información
            $table->enum('source', ['official', 'operator', 'citizen', 'seed', 'unverified'])
                  ->default('unverified');

            // Capacidad
            $table->unsignedInteger('capacity_total')->nullable();
            $table->unsignedInteger('capacity_available')->nullable();

            // Poblaciones aceptadas
            $table->boolean('accepts_children')->default(false);
            $table->boolean('accepts_elderly')->default(false);
            $table->boolean('accepts_pets')->default(false);

            // Servicios disponibles
            $table->boolean('has_water')->default(false);
            $table->boolean('has_food')->default(false);
            $table->boolean('has_medicine')->default(false);
            $table->boolean('has_power_charging')->default(false);

            // Contacto y notas
            $table->string('contact_phone', 30)->nullable();
            $table->text('notes')->nullable();

            // Urgencia/criticidad (1=baja, 2=media, 3=alta, 4=crítica)
            $table->unsignedTinyInteger('urgency_level')->default(1);

            // Auditoría
            $table->timestamp('last_verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Índices para filtros frecuentes
            $table->index(['status', 'category_id']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_points');
    }
};
