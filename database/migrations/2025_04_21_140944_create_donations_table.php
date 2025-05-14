<?php

use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $collection) {
            $collection->index('user_id');
            $collection->index('donation_request_id');
            $collection->double('amount');
            $collection->string('payment_status')->default('success');
            $collection->boolean('is_anonymous')->default(false);

            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};