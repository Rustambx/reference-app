<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_holders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tin', 14)->unique();
            $table->string('name');
            $table->string('region', 120)->nullable()->index();
            $table->string('district', 120)->nullable()->index();
            $table->string('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('responsible', 120)->nullable();

            $table->uuid('created_by')->nullable()->index();
            $table->uuid('updated_by')->nullable()->index();

            $table->timestamps();
        });
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
        DB::statement('CREATE INDEX budget_holders_name_trgm ON budget_holders USING gin (name gin_trgm_ops);');
        DB::statement('CREATE INDEX budget_holders_tin_trgm ON budget_holders USING gin (tin gin_trgm_ops);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_holders');
    }
};
