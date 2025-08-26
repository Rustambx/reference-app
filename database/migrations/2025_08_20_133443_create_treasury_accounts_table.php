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
        Schema::create('treasury_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('account', 34)->unique();
            $table->string('mfo', 9)->index();
            $table->string('name');
            $table->string('department')->nullable();
            $table->string('currency', 3)->index();

            $table->uuid('created_by')->nullable()->index();
            $table->uuid('updated_by')->nullable()->index();

            $table->timestamps();
        });

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
        DB::statement('CREATE INDEX treasury_accounts_name_trgm ON treasury_accounts USING gin (name gin_trgm_ops);');
        DB::statement('CREATE INDEX treasury_accounts_account_trgm ON treasury_accounts USING gin (account gin_trgm_ops);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treasury_accounts');
    }
};
