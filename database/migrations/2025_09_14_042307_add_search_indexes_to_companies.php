<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Singapore database indexes
        Schema::connection('companies_house_sg')->table('companies', function (Blueprint $table) {
            $table->index('name', 'idx_companies_name');
            $table->index('registration_number', 'idx_companies_registration_number');
            $table->index(['name', 'registration_number'], 'idx_companies_name_reg');
        });

        // Mexico database indexes
        Schema::connection('companies_house_mx')->table('companies', function (Blueprint $table) {
            $table->index('name', 'idx_companies_name');
            $table->index('brand_name', 'idx_companies_brand_name');
            $table->index('state_id', 'idx_companies_state_id');
            $table->index(['name', 'brand_name'], 'idx_companies_name_brand');
        });

        Schema::connection('companies_house_mx')->table('report_state', function (Blueprint $table) {
            $table->index(['state_id', 'report_id'], 'idx_report_state_state_report');
        });
    }

    public function down(): void
    {
        // Singapore database indexes
        Schema::connection('companies_house_sg')->table('companies', function (Blueprint $table) {
            $table->dropIndex('idx_companies_name');
            $table->dropIndex('idx_companies_registration_number');
            $table->dropIndex('idx_companies_name_reg');
        });

        // Mexico database indexes
        Schema::connection('companies_house_mx')->table('companies', function (Blueprint $table) {
            $table->dropIndex('idx_companies_name');
            $table->dropIndex('idx_companies_brand_name');
            $table->dropIndex('idx_companies_state_id');
            $table->dropIndex('idx_companies_name_brand');
        });

        Schema::connection('companies_house_mx')->table('report_state', function (Blueprint $table) {
            $table->dropIndex('idx_report_state_state_report');
        });
    }
};