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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('sales_rep')->after('password')->index();
            $table->string('phone', 50)->nullable()->after('role');
            $table->string('job_title', 100)->nullable()->after('phone');
            $table->string('department', 100)->nullable()->after('job_title');
            $table->boolean('is_active')->default(true)->after('department')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['role', 'phone', 'job_title', 'department', 'is_active']);
        });
    }
};
