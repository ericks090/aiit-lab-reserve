<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudyProgramRequest;
use App\Http\Requests\Admin\UpdateStudyProgramRequest;
use App\Models\StudyProgram;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminStudyProgramController extends Controller
{
    public function index(): View
    {
        $programs = StudyProgram::withCount(['users', 'teams'])
            ->orderBy('name')
            ->get();

        return view('admin.study-programs.index', compact('programs'));
    }

    public function create(): View
    {
        return view('admin.study-programs.create');
    }

    public function store(StoreStudyProgramRequest $request): RedirectResponse
    {
        $program = StudyProgram::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password,
            'is_active' => true,
        ]);

        AuditLogService::record('study_program.created', $program, [], [
            'name'  => $program->name,
            'email' => $program->email,
        ]);

        return redirect()->route('admin.study-programs.index')
            ->with('success', 'Program studi ' . $program->name . ' berhasil ditambahkan.');
    }

    public function edit(StudyProgram $studyProgram): View
    {
        return view('admin.study-programs.edit', ['program' => $studyProgram]);
    }

    public function update(UpdateStudyProgramRequest $request, StudyProgram $studyProgram): RedirectResponse
    {
        $oldValues = $studyProgram->only(['name', 'email', 'is_active']);

        $payload = [
            'name'      => $request->name,
            'email'     => $request->email,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->password;
        }

        $studyProgram->update($payload);

        $newValues = $studyProgram->only(['name', 'email', 'is_active']);
        if ($request->filled('password')) {
            $newValues['password'] = '[changed]';
        }

        AuditLogService::record('study_program.updated', $studyProgram, $oldValues, $newValues);

        return redirect()->route('admin.study-programs.index')
            ->with('success', 'Program studi ' . $studyProgram->name . ' berhasil diperbarui.');
    }
}
