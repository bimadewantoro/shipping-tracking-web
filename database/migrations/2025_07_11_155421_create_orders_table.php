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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();

            // Biteship order details
            $table->string('biteship_order_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('courier_code')->nullable();
            $table->string('courier_service')->nullable();
            $table->string('waybill_id')->nullable();

            // Sender information
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->text('sender_address');
            $table->string('sender_postal_code');
            $table->string('sender_area_id')->nullable();
            $table->decimal('sender_latitude', 10, 8)->nullable();
            $table->decimal('sender_longitude', 11, 8)->nullable();

            // Receiver information
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->text('receiver_address');
            $table->string('receiver_postal_code');
            $table->string('receiver_area_id')->nullable();
            $table->decimal('receiver_latitude', 10, 8)->nullable();
            $table->decimal('receiver_longitude', 11, 8)->nullable();

            // Package information
            $table->string('package_type')->default('package');
            $table->integer('package_weight'); // in grams
            $table->integer('package_length')->nullable(); // in cm
            $table->integer('package_width')->nullable(); // in cm
            $table->integer('package_height')->nullable(); // in cm
            $table->text('package_description')->nullable();
            $table->decimal('package_value', 12, 2)->nullable();

            // Pricing
            $table->decimal('shipping_cost', 12, 2)->nullable();
            $table->decimal('insurance_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();

            // Additional information
            $table->text('notes')->nullable();
            $table->json('biteship_response')->nullable(); // Store full API response
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('order_number');
            $table->index('biteship_order_id');
            $table->index('waybill_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
