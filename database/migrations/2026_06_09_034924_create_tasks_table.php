<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('number'); // per-project sequential number (e.g. WEB-12)
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('status', 20)->default('backlog'); // backlog|todo|in_progress|in_review|done
            $table->string('priority', 20)->default('none');  // none|low|medium|high|urgent
            $table->date('due_date')->nullable();
            $table->unsignedInteger('position')->default(0);   // ordering within a status column
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status', 'position']);
            $table->index('assignee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
