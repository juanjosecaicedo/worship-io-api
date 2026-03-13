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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3);
            $table->string('interval');
            $table->integer('trial_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('stripe_price_id')->nullable();
            $table->string('mercadopago_plan_id')->nullable();
            $table->string('paypal_plan_id')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->enum('feature', ['max_groups', 'max_members', 'max_songs', 'google_calendar', 'push_notifications', 'real_time_sync', 'priority_support', 'custom_branding', 'analytics']);
            $table->string('value');
            $table->unique(['subscription_plan_id', 'feature']);
            $table->timestamps();
        });


        //Subscripciones activas por usuario/grupo
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->enum('status', ['trialing', 'active', 'past_due', 'cancelled', 'expired']);
            $table->timestamp('trial_ends_at')->nullable();
            $table->date('current_period_start');
            $table->date('current_period_end');
            $table->date('cancelled_at')->nullable();
            $table->dateTime('ends_at')->comment('Actual expiration date (may be future if you cancel but have already paid)');
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('mercadopago_sub_id')->nullable();
            $table->string('paypal_sub_id')->nullable();
            $table->enum('payment_gateway', ['stripe', 'mercadopago', 'paypal'])->default('stripe');
            $table->timestamps();
            $table->index('user_id');
            $table->index('status');
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['paid', 'pending', 'failed', 'refunded']);
            $table->string('stripe_invoice_id')->nullable();
            $table->string('mercadopago_invoice_id')->nullable();
            $table->string('paypal_invoice_id')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        //Relación entre subscripción y grupos que cubre
        Schema::create('subscription_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->unique(['subscription_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
