<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            $table->string('logo_path')->nullable()->after('description');
            $table->string('email')->nullable()->after('logo_path');
            $table->string('phone', 30)->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->timestamp('verified_at')->nullable()->after('approved_at');
        });

        DB::table('organizations')
            ->whereNull('description')
            ->whereNotNull('bio')
            ->update(['description' => DB::raw('bio')]);

        DB::table('organizations')
            ->where('status', 'approved')
            ->whereNull('verified_at')
            ->whereNotNull('approved_at')
            ->update(['verified_at' => DB::raw('approved_at')]);
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'logo_path',
                'email',
                'phone',
                'address',
                'verified_at',
            ]);
        });
    }
};
