<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $blueprint) {
            $blueprint->foreignId('category_id')->nullable()->change();
            $blueprint->foreignId('account_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $blueprint) {
            // Isso pode falhar se houver registros nulos no banco
            $blueprint->foreignId('category_id')->nullable(false)->change();
            $blueprint->foreignId('account_id')->nullable(false)->change();
        });
    }
};
