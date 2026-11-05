<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contributor;
use App\Models\GithubTeam;
use Illuminate\Http\Request;

class ContributorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $contributors = Contributor::with('githubTeam')
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.contributors.index', compact('contributors'));
    }

    public function create()
    {
        $githubTeams = GithubTeam::ordered()->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.contributors.partials.form', [
                    'contributor' => null,
                    'githubTeams' => $githubTeams,
                ])->render(),
            ]);
        }

        return view('admin.contributors.create', compact('githubTeams'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $contributor = Contributor::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kontributor berhasil ditambahkan.',
                'data' => $contributor,
            ]);
        }

        return redirect()->route('admin.contributors.index')
            ->with('success', 'Kontributor berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $contributor = Contributor::with('githubTeam')->findOrFail($id);

        return view('admin.contributors.show', compact('contributor'));
    }

    public function edit(string $id)
    {
        $contributor = Contributor::findOrFail($id);
        $githubTeams = GithubTeam::ordered()->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.contributors.partials.form', compact('contributor', 'githubTeams'))->render(),
                'data' => $contributor,
            ]);
        }

        return view('admin.contributors.edit', compact('contributor', 'githubTeams'));
    }

    public function update(Request $request, string $id)
    {
        $contributor = Contributor::findOrFail($id);
        $validated = $this->validated($request);
        $contributor->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kontributor berhasil diperbarui.',
                'data' => $contributor,
            ]);
        }

        return redirect()->route('admin.contributors.index')
            ->with('success', 'Kontributor berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $contributor = Contributor::findOrFail($id);
        $contributor->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kontributor berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.contributors.index')
            ->with('success', 'Kontributor berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image_url' => 'required|url|max:500',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'github_team_id' => 'nullable|exists:github_teams,id',
        ]);

        $validated['order'] = (int) ($validated['order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['github_team_id'] = $validated['github_team_id'] ?: null;

        return $validated;
    }
}
