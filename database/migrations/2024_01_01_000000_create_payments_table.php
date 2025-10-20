<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('provider');
            $table->string('provider_reference')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('charged_amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->enum('status', [
                'pending',
                'processing', 
                'successful',
                'failed',
                'cancelled',
                'refunded'
            ])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('customer_email');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('redirect_url')->nullable();
            $table->text('checkout_url')->nullable();
            $table->string('access_code')->nullable();
            $table->json('metadata')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['provider', 'status']);
            $table->index(['customer_email']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
