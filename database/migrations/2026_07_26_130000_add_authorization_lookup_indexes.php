<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->index(['role', 'is_approved'], 'users_role_approval_index');
        });

        Schema::table('organizations', function (Blueprint $table): void {
            $table->index('status', 'organizations_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropIndex('organizations_status_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_approval_index');
        });
    }
};
