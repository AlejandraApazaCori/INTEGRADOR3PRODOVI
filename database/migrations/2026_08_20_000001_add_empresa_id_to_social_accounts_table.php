<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL necesita otro indice sobre user_id antes de eliminar el indice
        // unico compuesto que actualmente utiliza para la clave foranea.
        if (! Schema::hasIndex('social_accounts', 'social_accounts_user_id_lookup_index')) {
            Schema::table('social_accounts', function (Blueprint $table) {
                $table->index('user_id', 'social_accounts_user_id_lookup_index');
            });
        }

        Schema::table('social_accounts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'provider']);
            $table->dropUnique(['provider', 'provider_user_id']);
            $table->foreignId('empresa_id')->nullable()->after('user_id')->constrained('empresas')->nullOnDelete();
            $table->unique(['user_id', 'empresa_id', 'provider'], 'social_accounts_user_company_provider_unique');
            $table->unique(['empresa_id', 'provider', 'provider_user_id'], 'social_accounts_company_provider_account_unique');
        });
    }

    public function down(): void
    {
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->dropUnique('social_accounts_user_company_provider_unique');
            $table->dropUnique('social_accounts_company_provider_account_unique');
            $table->dropConstrainedForeignId('empresa_id');
            $table->unique(['user_id', 'provider']);
            $table->unique(['provider', 'provider_user_id']);
        });

        if (Schema::hasIndex('social_accounts', 'social_accounts_user_id_lookup_index')) {
            Schema::table('social_accounts', function (Blueprint $table) {
                $table->dropIndex('social_accounts_user_id_lookup_index');
            });
        }
    }
};
