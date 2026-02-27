<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('zip_code', 10)->nullable()->after('state');
            $table->date('birth_date')->nullable()->after('zip_code');
            $table->string('role', 20)->default('user')->after('birth_date');
            $table->softDeletes();

            $table->index('role');
            $table->index(['state', 'city']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['state', 'city']);
            $table->dropSoftDeletes();
            $table->dropColumn(['phone', 'address', 'city', 'state', 'zip_code', 'birth_date', 'role']);
        });
    }
};
