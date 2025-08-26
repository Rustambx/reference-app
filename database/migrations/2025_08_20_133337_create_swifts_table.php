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
        Schema::create('swifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('swift_code', 11)->unique();
            $table->string('bank_name');
            $table->string('country', 3)->index();
            $table->string('city', 120)->nullable()->index();
            $table->string('address')->nullable();

            $table->uuid('created_by')->nullable()->index();
            $table->uuid('updated_by')->nullable()->index();

            $table->timestamps();
        });

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
        DB::statement('CREATE INDEX swifts_bank_name_trgm ON swifts USING gin (bank_name gin_trgm_ops);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swifts');
    }
};
