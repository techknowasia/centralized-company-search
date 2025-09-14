<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Singapore database - check if indexes exist first
        $this->addIndexIfNotExists('companies_house_sg', 'companies', 'name', 'idx_companies_name');
        $this->addIndexIfNotExists('companies_house_sg', 'companies', 'registration_number', 'idx_companies_registration_number');

        // Mexico database - check if indexes exist first
        $this->addIndexIfNotExists('companies_house_mx', 'companies', 'name', 'idx_companies_name');
        $this->addIndexIfNotExists('companies_house_mx', 'companies', 'brand_name', 'idx_companies_brand_name');
        $this->addIndexIfNotExists('companies_house_mx', 'companies', 'state_id', 'idx_companies_state_id');
    }

    public function down(): void
    {
        // Drop Singapore indexes
        $this->dropIndexIfExists('companies_house_sg', 'companies', 'idx_companies_name');
        $this->dropIndexIfExists('companies_house_sg', 'companies', 'idx_companies_registration_number');

        // Drop Mexico indexes
        $this->dropIndexIfExists('companies_house_mx', 'companies', 'idx_companies_name');
        $this->dropIndexIfExists('companies_house_mx', 'companies', 'idx_companies_brand_name');
        $this->dropIndexIfExists('companies_house_mx', 'companies', 'idx_companies_state_id');
    }

    private function addIndexIfNotExists(string $connection, string $table, string $column, string $indexName): void
    {
        $indexExists = DB::connection($connection)
            ->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

        if (empty($indexExists)) {
            Schema::connection($connection)->table($table, function (Blueprint $table) use ($column, $indexName) {
                $table->index($column, $indexName);
            });
        }
    }

    private function dropIndexIfExists(string $connection, string $table, string $indexName): void
    {
        $indexExists = DB::connection($connection)
            ->select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

        if (!empty($indexExists)) {
            Schema::connection($connection)->table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};