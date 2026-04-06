<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Notifications core tracking table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('tenant_id')->nullable()->index();
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Logs for every notification dispatch attempt
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('notification_id')->nullable();
            $table->string('driver');
            $table->string('status');
            $table->text('response')->nullable();
            $table->timestamps();
        });

        // Scheduled queues for delayed / database driven ones
        Schema::create('scheduled_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->string('notifiable_type')->nullable();
            $table->string('driver');
            $table->longText('notification');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('schedule_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'schedule_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_notifications');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notifications');
    }
};
