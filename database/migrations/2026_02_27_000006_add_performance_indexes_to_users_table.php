<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['deleted_at', 'created_at']);
            $table->index(['deleted_at', 'name']);
            $table->index(['deleted_at', 'role', 'state', 'city']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
            DB::statement('CREATE INDEX idx_users_name_trgm ON users USING GIN (name gin_trgm_ops)');
            DB::statement('CREATE INDEX idx_users_email_trgm ON users USING GIN (email gin_trgm_ops)');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_users_email_trgm');
            DB::statement('DROP INDEX IF EXISTS idx_users_name_trgm');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deleted_at', 'role', 'state', 'city']);
            $table->dropIndex(['deleted_at', 'name']);
            $table->dropIndex(['deleted_at', 'created_at']);
        });
    }
};
