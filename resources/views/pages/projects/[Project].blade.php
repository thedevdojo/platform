<?php

use App\Models\Project;

use function Laravel\Folio\{middleware, name};

middleware(['auth']);
name('projects.show');

?>

<x-layouts.app :title="$project->name">
    <div class="flex h-full flex-col">
        <livewire:project-board :project="$project" :key="'board-'.$project->id" />
    </div>
    <livewire:task-detail :key="'detail-'.$project->id" />
</x-layouts.app>
