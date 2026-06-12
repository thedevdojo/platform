<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('key', 10);
            $table->text('description')->nullable();
            $table->string('color', 20)->default('indigo');
            $table->string('icon', 40)->default('rocket-launch');
            $table->string('status', 20)->default('active'); // active | archived
            $table->timestamps();

            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
