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
        Schema::create('wallet_envs', function (Blueprint $table) {
            $table->id();
            $table->string('chain')->unique()->nullable();
            $table->string('xpub')->unique()->nullable();
            $table->longText('mnemonic')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_envs');
    }
};
