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
        Schema::create('donation_requests', function (Blueprint $collection) {
            $collection->index('org_id');
            $collection->string('title');
            $collection->string('banner_url')->nullable();
            $collection->boolean("is_urgent");
            $collection->string('category');
            $collection->json('other_images')->nullable();
            $collection->string('description');
            $collection->double('amount_needed');
            $collection->double('amount_received')->default(0);
            $collection->string('status')->default('pending');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_requests');
    }
};
