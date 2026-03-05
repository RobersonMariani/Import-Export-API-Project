<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->text('error_message')->nullable()->after('metadata');
        });

        Schema::table('exports', function (Blueprint $table) {
            $table->text('error_message')->nullable()->after('filters');
        });
    }

    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('error_message');
        });

        Schema::table('exports', function (Blueprint $table) {
            $table->dropColumn('error_message');
        });
    }
};
