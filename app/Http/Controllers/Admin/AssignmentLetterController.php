<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssignmentLetter;
use App\Models\Jasa;
use App\Models\Project;
use App\Services\ProjectFinanceService;
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
        $jasas = Jasa::active()->orderBy('name')->get();

        return view('admin.projects.assignment-letters.create', [
            'project' => $project,
            'letter' => null,
            'jasas' => $jasas,
        ]);
    }

    public function store(Request $request, string $projectId)
    {
        $project = Project::findOrFail($projectId);
        [$letterData, $assignees, $jasaItems] = $this->validateLetter($request);

        try {
            $letter = DB::transaction(function () use ($project, $letterData, $assignees, $jasaItems) {
                $letter = $project->assignmentLetters()->create([
                    ...$letterData,
                    'number' => AssignmentLetter::generateNumber(),
                ]);
                $this->syncAssignees($letter, $assignees);
                $this->syncJasaItems($letter, $jasaItems);
                ProjectFinanceService::syncJasaFromAssignmentLetters($project->fresh());

                return $letter;
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()
            ->route('admin.projects.assignment-letters.show', [$project, $letter])
            ->with('success', 'Surat tugas berhasil dibuat.');
    }

    public function show(string $projectId, string $letterId)
    {
        $project = Project::with('customer')->findOrFail($projectId);
        $letter = AssignmentLetter::with(['assignees', 'jasaItems'])
            ->where('project_id', $project->id)
            ->findOrFail($letterId);

        return view('admin.projects.assignment-letters.show', compact('project', 'letter'));
    }

    public function edit(string $projectId, string $letterId)
    {
        $project = Project::findOrFail($projectId);
        $letter = AssignmentLetter::with(['assignees', 'jasaItems'])
            ->where('project_id', $project->id)
            ->findOrFail($letterId);
        $jasas = Jasa::active()->orderBy('name')->get();

        // Keep selected jasa even if later deactivated.
        $selectedIds = $letter->jasaItems->pluck('jasa_id')->filter()->all();
        if ($selectedIds) {
            $extra = Jasa::whereIn('id', $selectedIds)->get();
            $jasas = $jasas->merge($extra)->unique('id')->sortBy('name')->values();
        }

        return view('admin.projects.assignment-letters.edit', compact('project', 'letter', 'jasas'));
    }

    public function update(Request $request, string $projectId, string $letterId)
    {
        $project = Project::findOrFail($projectId);
        $letter = AssignmentLetter::where('project_id', $project->id)->findOrFail($letterId);
        [$letterData, $assignees, $jasaItems] = $this->validateLetter($request);

        try {
            DB::transaction(function () use ($project, $letter, $letterData, $assignees, $jasaItems) {
                $letter->update($letterData);
                $this->syncAssignees($letter, $assignees);
                $this->syncJasaItems($letter, $jasaItems);
                ProjectFinanceService::syncJasaFromAssignmentLetters($project->fresh());
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()
            ->route('admin.projects.assignment-letters.show', [$project, $letter])
            ->with('success', 'Surat tugas berhasil diperbarui.');
    }

    public function destroy(string $projectId, string $letterId)
    {
        $project = Project::findOrFail($projectId);
        $letter = AssignmentLetter::where('project_id', $project->id)->findOrFail($letterId);

        try {
            DB::transaction(function () use ($project, $letter) {
                $letter->delete();
                ProjectFinanceService::syncJasaFromAssignmentLetters($project->fresh());
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('admin.projects.show', $project)
                ->with('error', collect($e->errors())->flatten()->first() ?? 'Surat tugas tidak bisa dihapus.');
        }

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
            'jasa_items' => 'nullable|array',
            'jasa_items.*.jasa_id' => 'required|exists:jasas,id',
            'jasa_items.*.description' => 'nullable|string|max:255',
            'jasa_items.*.quantity' => 'required|integer|min:1',
            'jasa_items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'assignees.required' => 'Minimal 1 orang yang bertugas harus diisi.',
            'assignees.min' => 'Minimal 1 orang yang bertugas harus diisi.',
            'assignees.*.name.required' => 'Nama yang bertugas wajib diisi.',
            'assignees.*.gender.required' => 'Jenis kelamin wajib dipilih.',
            'assignees.*.ktp.required' => 'Nomor KTP wajib diisi.',
            'assignees.*.phone.required' => 'Nomor HP wajib diisi.',
            'jasa_items.*.jasa_id.required' => 'Jasa wajib dipilih.',
            'jasa_items.*.quantity.required' => 'Qty jasa wajib diisi.',
            'jasa_items.*.unit_price.required' => 'Harga jasa wajib diisi.',
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

        $jasaCatalog = Jasa::whereIn(
            'id',
            collect($validated['jasa_items'] ?? [])->pluck('jasa_id')->filter()->unique()->all()
        )->get()->keyBy('id');

        $jasaItems = collect($validated['jasa_items'] ?? [])
            ->map(function ($row, $index) use ($jasaCatalog) {
                $jasaId = (int) ($row['jasa_id'] ?? 0);
                $jasa = $jasaCatalog->get($jasaId);
                $qty = max(1, (int) ($row['quantity'] ?? 1));
                $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
                $description = trim((string) ($row['description'] ?? ''));
                if ($description === '' && $jasa) {
                    $description = $jasa->name;
                }

                return [
                    'sort_order' => $index,
                    'jasa_id' => $jasaId ?: null,
                    'description' => $description !== '' ? $description : 'Jasa',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => round($qty * $unitPrice, 2),
                ];
            })
            ->values()
            ->all();

        unset($validated['assignees'], $validated['jasa_items']);

        return [$validated, $assignees, $jasaItems];
    }

    private function syncAssignees(AssignmentLetter $letter, array $assignees): void
    {
        $letter->assignees()->delete();
        foreach ($assignees as $row) {
            $letter->assignees()->create($row);
        }
    }

    private function syncJasaItems(AssignmentLetter $letter, array $jasaItems): void
    {
        $letter->jasaItems()->delete();
        foreach ($jasaItems as $row) {
            $letter->jasaItems()->create($row);
        }
    }
}
