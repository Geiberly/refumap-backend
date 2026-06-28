<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('operator')->after('email'); // admin | operator
            $table->string('organization')->nullable()->after('password');
            $table->string('position')->nullable()->after('organization');
            $table->string('city')->nullable()->after('position');
            $table->string('state')->nullable()->after('city');
            $table->text('motivation')->nullable()->after('state');
            $table->string('coverage_area')->nullable()->after('motivation');
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'disabled'])->default('approved')->after('role');
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'organization', 'position', 'city', 'state', 
                'motivation', 'coverage_area', 'status', 'rejection_reason'
            ]);
        });
    }
};
