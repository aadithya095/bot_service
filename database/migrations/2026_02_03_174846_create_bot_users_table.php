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
        Schema::create('bot_users', function (Blueprint $table) {
            $table->id();

            $table->string('channel');
            $table->string('channel_user_id')->index();

            $table->timestamp('last_received_message_timestamp')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['channel', 'channel_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_users');
    }
};
