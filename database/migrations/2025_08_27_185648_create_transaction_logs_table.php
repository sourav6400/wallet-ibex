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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('wallet_id')->nullable();
            $table->string('type')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('token')->nullable();
            $table->string('chain')->nullable();
            $table->string('amount')->nullable();
            $table->string('status')->nullable();
            $table->longText('response')->nullable();
            $table->longText('real_balance_before_send')->nullable();
            $table->longText('fake_balance_before_send')->nullable();
            $table->longText('real_balance_after_send')->nullable();
            $table->longText('fake_balance_after_send')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
