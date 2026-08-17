<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libelula_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plan');
            $table->string('identifier')->unique();
            $table->string('libelula_transaction_id')->nullable()->unique();
            $table->string('collection_code')->nullable();
            $table->text('payment_url')->nullable();
            $table->text('qr_url')->nullable();
            $table->string('customer_email');
            $table->string('customer_name')->nullable();
            $table->string('document_type_code', 10);
            $table->string('document_number', 50);
            $table->string('document_complement', 20)->nullable();
            $table->string('document_extension', 20)->nullable();
            $table->string('business_name');
            $table->text('description')->nullable();
            $table->char('currency', 3)->default('BOB');
            $table->decimal('expected_amount', 12, 2);
            $table->string('status', 30)->default('creating');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'plan_id', 'status']);
        });

        Schema::create('libelula_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libelula_transaction_record_id')->nullable()
                ->constrained('libelula_transactions')->nullOnDelete();
            $table->string('libelula_transaction_id')->nullable();
            $table->string('identifier')->nullable();
            $table->string('event_type')->default('payment_success');
            $table->string('source')->default('callback');
            $table->json('payload')->nullable();
            $table->string('processing_status', 30)->default('received');
            $table->text('error_message')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('estado');
            $table->string('provider_transaction_id')->nullable()->after('provider');
            $table->string('provider_reference')->nullable()->after('provider_transaction_id');
            $table->unique(['provider', 'provider_transaction_id'], 'pagos_provider_transaction_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropUnique('pagos_provider_transaction_unique');
            $table->dropColumn(['provider', 'provider_transaction_id', 'provider_reference']);
        });

        Schema::dropIfExists('libelula_events');
        Schema::dropIfExists('libelula_transactions');
    }
};
