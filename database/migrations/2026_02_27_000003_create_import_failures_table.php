<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_failures', function (Blueprint $table) {
            $table->id();
            $table->uuid('import_id');
            $table->unsignedInteger('line_number');
            $table->jsonb('payload');
            $table->text('error_message');
            $table->timestamps();

            $table->foreign('import_id')->references('id')->on('imports')->onDelete('cascade');
            $table->index('import_id');
            $table->index(['import_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_failures');
    }
};
