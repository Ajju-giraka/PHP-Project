@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tasks</h2>
            <a href="{{ route('tasks.create') }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                Add New Task
            </a>
        </div>

        <!-- Project Filter -->
        <div class="mb-6">
            <form method="GET" class="flex items-end space-x-4">
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Filter by Project
                    </label>
                    <select name="project_id" id="project_id" 
                            onchange="this.form.submit()"
                            class="w-64 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" 
                                    {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedProjectId)
                    <a href="{{ route('tasks.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        Clear Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Tasks List -->
        @if($tasks->count() > 0)
            <ul id="tasks-list" class="space-y-3">
                @foreach($tasks as $task)
                    <li data-task-id="{{ $task->id }}" 
                        class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex justify-between items-center cursor-move hover:bg-gray-100 transition duration-200">
                        <div class="flex items-center space-x-4">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-medium">
                                Priority: {{ $task->priority }}
                            </span>
                            <span class="text-gray-800 font-medium">{{ $task->name }}</span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                {{ $task->project->name }}
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('tasks.edit', $task) }}" 
                               class="text-yellow-600 hover:text-yellow-800 transition duration-200">
                                Edit
                            </a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this task?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-800 transition duration-200 ml-2">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500 text-lg">No tasks found.</p>
                @if(!$selectedProjectId)
                    <a href="{{ route('tasks.create') }}" 
                       class="text-blue-500 hover:text-blue-700 mt-2 inline-block">
                        Create your first task
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tasksList = document.getElementById('tasks-list');
        
        if (tasksList) {
            new Sortable(tasksList, {
                animation: 150,
                ghostClass: 'bg-blue-50',
                onEnd: function(evt) {
                    const taskIds = Array.from(tasksList.children).map(item => item.dataset.taskId);
                    
                    fetch('{{ route("tasks.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            task_ids: taskIds
                        })
                    }).then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              // Update priority numbers visually
                              tasksList.querySelectorAll('li').forEach((item, index) => {
                                  const prioritySpan = item.querySelector('.bg-blue-100');
                                  if (prioritySpan) {
                                      prioritySpan.textContent = `Priority: ${index + 1}`;
                                  }
                              });
                          }
                      });
                }
            });
        }
    });
</script>
@endpush
@endsection
