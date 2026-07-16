<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssignmentLetter;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentLetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(string $projectId)
    {
        $project = Project::with(['assignmentLetters.assignees'])->findOrFail($projectId);

        return view('admin.projects.assignment-letters.index', compact('project'));
    }

    public function create(string $projectId)
    {
        $project = Project::findOrFail($projectId);

        return view('admin.projects.assignment-letters.create', [
            'project' => $project,
            'letter' => null,
        ]);
    }

    public function store(Request $request, string $projectId)
    {
        $project = Project::findOrFail($projectId);
        [$letterData, $assignees] = $this->validateLetter($request);

        $letter = DB::transaction(function () use ($project, $letterData, $assignees) {
            $letter = $project->assignmentLetters()->create([
                ...$letterData,
                'number' => AssignmentLetter::generateNumber(),
            ]);
            $this->syncAssignees($letter, $assignees);

            return $letter;
        });

        return redirect()
            ->route('admin.projects.assignment-letters.show', [$project, $letter])
            ->with('success', 'Surat tugas berhasil dibuat.');
    }

    public function show(string $projectId, string $letterId)
    {
        $project = Project::with('customer')->findOrFail($projectId);
        $letter = AssignmentLetter::with('assignees')
            ->where('project_id', $project->id)
            ->findOrFail($letterId);

        return view('admin.projects.assignment-letters.show', compact('project', 'letter'));
    }

    public function edit(string $projectId, string $letterId)
    {
        $project = Project::findOrFail($projectId);
        $letter = AssignmentLetter::with('assignees')
            ->where('project_id', $project->id)
            ->findOrFail($letterId);

        return view('admin.projects.assignment-letters.edit', compact('project', 'letter'));
    }

    public function update(Request $request, string $projectId, string $letterId)
    {
        $project = Project::findOrFail($projectId);
        $letter = AssignmentLetter::where('project_id', $project->id)->findOrFail($letterId);
        [$letterData, $assignees] = $this->validateLetter($request);

        DB::transaction(function () use ($letter, $letterData, $assignees) {
            $letter->update($letterData);
            $this->syncAssignees($letter, $assignees);
        });

        return redirect()
            ->route('admin.projects.assignment-letters.show', [$project, $letter])
            ->with('success', 'Surat tugas berhasil diperbarui.');
    }

    public function destroy(string $projectId, string $letterId)
    {
        $project = Project::findOrFail($projectId);
        $letter = AssignmentLetter::where('project_id', $project->id)->findOrFail($letterId);
        $letter->delete();

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Surat tugas berhasil dihapus.');
    }

    private function validateLetter(Request $request): array
    {
        $validated = $request->validate([
            'letter_date' => 'required|date',
            'subject' => 'nullable|string|max:255',
            'task_description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'assignees' => 'required|array|min:1',
            'assignees.*.name' => 'required|string|max:255',
            'assignees.*.gender' => 'required|in:L,P',
            'assignees.*.ktp' => 'required|string|max:32',
            'assignees.*.phone' => 'required|string|max:32',
        ], [
            'assignees.required' => 'Minimal 1 orang yang bertugas harus diisi.',
            'assignees.min' => 'Minimal 1 orang yang bertugas harus diisi.',
            'assignees.*.name.required' => 'Nama yang bertugas wajib diisi.',
            'assignees.*.gender.required' => 'Jenis kelamin wajib dipilih.',
            'assignees.*.ktp.required' => 'Nomor KTP wajib diisi.',
            'assignees.*.phone.required' => 'Nomor HP wajib diisi.',
        ]);

        $assignees = collect($validated['assignees'] ?? [])
            ->map(function ($row, $index) {
                return [
                    'sort_order' => $index,
                    'name' => trim((string) ($row['name'] ?? '')),
                    'gender' => $row['gender'] ?? 'L',
                    'ktp' => trim((string) ($row['ktp'] ?? '')),
                    'phone' => trim((string) ($row['phone'] ?? '')),
                ];
            })
            ->filter(fn ($row) => $row['name'] !== '')
            ->values()
            ->all();

        if (count($assignees) < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'assignees' => ['Minimal 1 orang yang bertugas harus diisi.'],
            ]);
        }

        unset($validated['assignees']);

        return [$validated, $assignees];
    }

    private function syncAssignees(AssignmentLetter $letter, array $assignees): void
    {
        $letter->assignees()->delete();
        foreach ($assignees as $row) {
            $letter->assignees()->create($row);
        }
    }
}
