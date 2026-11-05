<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GithubTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GithubTeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $teams = GithubTeam::with(['contributors' => fn ($q) => $q->orderBy('order')])
            ->withCount('contributors')
            ->ordered()
            ->get();

        return view('admin.github-teams.index', compact('teams'));
    }

    public function create()
    {
        return view('admin.github-teams.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        GithubTeam::create($validated);

        return redirect()
            ->route('admin.github-teams.index')
            ->with('success', 'Tim GitHub berhasil ditambahkan.');
    }

    public function edit(GithubTeam $githubTeam)
    {
        return view('admin.github-teams.edit', ['team' => $githubTeam]);
    }

    public function update(Request $request, GithubTeam $githubTeam)
    {
        $validated = $this->validated($request);
        $oldLogin = $githubTeam->githubLogin();
        $githubTeam->update($validated);

        $newLogin = $githubTeam->fresh()->githubLogin();
        if ($oldLogin) {
            Cache::forget('github_team_repos:' . strtolower($oldLogin));
        }
        if ($newLogin && $newLogin !== $oldLogin) {
            Cache::forget('github_team_repos:' . strtolower($newLogin));
        }

        return redirect()
            ->route('admin.github-teams.index')
            ->with('success', 'Tim GitHub berhasil diperbarui.');
    }

    public function destroy(GithubTeam $githubTeam)
    {
        $login = $githubTeam->githubLogin();
        $githubTeam->delete();

        if ($login) {
            Cache::forget('github_team_repos:' . strtolower($login));
        }

        return redirect()
            ->route('admin.github-teams.index')
            ->with('success', 'Tim GitHub berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'github_url' => 'required|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        $url = trim($validated['github_url']);
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://github.com/' . ltrim($url, '/');
        }
        $validated['github_url'] = $url;

        $probe = new GithubTeam(['github_url' => $url]);
        if (! $probe->githubLogin()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'github_url' => 'Masukkan URL atau username GitHub yang valid, contoh: https://github.com/nama-tim',
            ]);
        }

        return $validated;
    }
}
