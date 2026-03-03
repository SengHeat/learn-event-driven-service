<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_id')->unique();
            $table->string('version', 10);
            $table->string('event_type', 100)->index();
            $table->timestampTz('occurred_at');
            $table->string('producer', 100);
            $table->string('aggregate_id', 100);
            $table->string('aggregate_type', 100);
            $table->jsonb('headers')->nullable();
            $table->jsonb('data');
            $table->string('direction', 20)->default('subscribe')->comment('subscribe = inbound | publish = outbound');$table->string('status', 20)->default('pending');
            $table->integer('data_version')->default(1)->after('data')->comment('Schema version of event data e.g. 1, 2, 3');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('domain_events');
    }
};
