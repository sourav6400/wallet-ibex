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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // if user is deleted, wallets will also be deleted
            $table->string('name')->nullable();
            $table->string('chain')->nullable();
            $table->string('token')->nullable();
            $table->string('address')->unique()->nullable();
            $table->string('public_key')->unique()->nullable();
            $table->string('private_key')->unique()->nullable();
            $table->decimal('real_token', 15, 4)->default(0);
            $table->decimal('fake_token', 15, 4)->default(0);
            $table->decimal('usd_value', 15, 4)->default(0);
            $table->enum('active_transaction_type', ['real', 'fake'])->default('real');
            $table->enum('status', ['Active', 'Deactivate'])->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
