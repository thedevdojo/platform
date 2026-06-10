<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('logo')->nullable();
            $table->json('screenshots')->nullable();
            $table->string('pricing')->default('free');
            $table->string('status')->default('draft');
            $table->boolean('featured')->default(false);
            $table->timestamp('launched_at')->nullable();
            $table->unsignedInteger('votes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'launched_at']);
            $table->index('votes_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
