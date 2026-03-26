<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('shop_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['shop', 'user']);
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('message_id')->constrained()->cascadeOnDelete();
            $table->boolean('notify')->default(0);
            $table->timestamp('last_read')->nullable();
            $table->timestamps();
        });

        Schema::table('user_profiles', function ($table): void {
            $table->json('notifications')->after('contact')->nullable();
        });
        Schema::table('shops', function ($table): void {
            $table->json('notifications')->after('settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participants');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::table('user_profiles', function ($table): void {
            $table->dropColumn('notifications');
        });
        Schema::table('shops', function ($table): void {
            $table->dropColumn('notifications');
        });
    }
};
