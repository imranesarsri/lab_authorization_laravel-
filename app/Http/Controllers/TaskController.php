<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Exports\TaskExport;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\FormTaskRequest;
use App\Repositories\ProjectRepository;

class TaskController extends Controller
{
    private $TaskRepository;
    private $ProjectRepository;
    public function __construct(TaskRepository $TaskRepository, ProjectRepository $ProjectRepository)
    {
        $this->TaskRepository = $TaskRepository;
        $this->ProjectRepository = $ProjectRepository;
    }

    public function index(Request $request)
    {
        $ProjectsFilter = $this->ProjectRepository->projectFilters();
        $Tasks = $this->TaskRepository->searchAndFilter($request);
        $Task = $request->route('id');
        if ($Task && !$request->ajax()) {
            $Tasks = $this->TaskRepository->filterByProject($Task);
            return view('Tasks.index', compact('Tasks', 'ProjectsFilter', 'Task'));
        }
        if ($request->ajax()) {
            return view('Tasks.search', compact('Tasks'))->render();
        }
        return view('Tasks.index', compact('Tasks', 'ProjectsFilter', 'Task'));
    }

    public function create()
    {
        if (Gate::denies('create', Task::class)) {
            return abort(403);
        }
        $ProjectsFilter = $this->ProjectRepository->projectFilters();
        return view('Tasks.create', compact('ProjectsFilter'));
    }


    public function store(FormTaskRequest $request)
    {
        $this->TaskRepository->create($request->validated());
        return redirect()->route('tasks.index')->with('success', 'Tâche créée avec succès !');
    }


    public function show(Task $task)
    {
        return view('Tasks.show', compact('task'));
    }


    public function edit(Task $task)
    {
        if (Gate::denies('update', $task)) {
            return abort(403);
        }
        $ProjectsFilter = $this->ProjectRepository->projectFilters();
        return view('Tasks.edit', compact('task', 'ProjectsFilter'));
    }


    public function update(FormTaskRequest $request, Task $task)
    {
        $this->TaskRepository->update($task, $request->validated());
        return redirect()->route('tasks.index')->with('success', 'Tâche mise à jour avec succès !');
    }


    public function destroy(Task $task)
    {
        if (Gate::denies('delete', $task)) {
            return abort(403);
        }
        $this->TaskRepository->delete($task);
        return redirect()->route('tasks.index')->with('success', 'Tâche supprimée avec succès !');
    }

    public function export_tasks()
    {
        return Excel::download(new TaskExport, 'tasks.xlsx');
    }
}