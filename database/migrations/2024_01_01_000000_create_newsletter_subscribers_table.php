<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('statamic-newsletter.database.connection');
        $table = config('statamic-newsletter.database.table', 'newsletter_subscribers');

        Schema::connection($connection)->create($table, function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('subscribed')->nullable();
            $table->timestamps();
            $table->index(['status', 'email']);
        });
    }

    public function down(): void
    {
        $connection = config('statamic-newsletter.database.connection');
        $table = config('statamic-newsletter.database.table', 'newsletter_subscribers');

        Schema::connection($connection)->dropIfExists($table);
    }
};
