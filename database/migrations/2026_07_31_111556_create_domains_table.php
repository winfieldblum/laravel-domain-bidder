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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('hostname')->unique();
            $table->string('display_name');
            $table->string('tagline');
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->string('mail_from_address');
            $table->string('mail_from_name');
            $table->string('notification_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
