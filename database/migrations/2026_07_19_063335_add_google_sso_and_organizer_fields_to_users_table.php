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
            $table->string('google_id')->nullable()->unique()->after('password');
            $table->string('avatar')->nullable()->after('google_id');
            $table->string('organization_name')->nullable()->after('avatar');
            $table->string('organization_slug')->nullable()->unique()->after('organization_name');
            $table->text('organization_bio')->nullable()->after('organization_slug');
            $table->boolean('is_approved')->default(false)->after('organization_bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_id',
                'avatar',
                'organization_name',
                'organization_slug',
                'organization_bio',
                'is_approved',
            ]);
        });
    }
};
