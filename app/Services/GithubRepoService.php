<?php

namespace App\Services;

use App\Models\GithubTeam;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GithubRepoService
{
    /**
     * Ambil daftar repo publik untuk satu tim (cache 30 menit).
     *
     * @return array{login: ?string, repos: array<int, array<string, mixed>>, error: ?string}
     */
    public function reposForTeam(GithubTeam $team): array
    {
        $login = $team->githubLogin();
        if (! $login) {
            return [
                'login' => null,
                'repos' => [],
                'error' => 'Link GitHub tidak valid.',
            ];
        }

        $cacheKey = 'github_team_repos:' . strtolower($login);

        try {
            return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($login) {
                return $this->fetchRepos($login);
            });
        } catch (\Throwable $e) {
            Log::warning('Cache repo GitHub gagal, fetch langsung', [
                'login' => $login,
                'message' => $e->getMessage(),
            ]);

            return $this->fetchRepos($login);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, GithubTeam>  $teams
     * @return array<int, array{team: GithubTeam, login: ?string, repos: array, error: ?string}>
     */
    public function reposForTeams($teams): array
    {
        $result = [];

        foreach ($teams as $team) {
            $payload = $this->reposForTeam($team);
            $result[] = [
                'team' => $team,
                'login' => $payload['login'],
                'repos' => $payload['repos'],
                'error' => $payload['error'],
            ];
        }

        return $result;
    }

    /**
     * @return array{login: string, repos: array<int, array<string, mixed>>, error: ?string}
     */
    protected function fetchRepos(string $login): array
    {
        $endpoints = [
            "https://api.github.com/orgs/{$login}/repos",
            "https://api.github.com/users/{$login}/repos",
        ];

        $lastError = null;

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => config('app.name', 'IrTeguhSolution'),
                ])
                    ->timeout(12)
                    ->get($endpoint, [
                        'per_page' => 100,
                        'sort' => 'updated',
                        'direction' => 'desc',
                    ]);

                if ($response->status() === 404) {
                    continue;
                }

                if (! $response->successful()) {
                    $lastError = 'GitHub API merespons ' . $response->status() . '.';
                    continue;
                }

                $items = $response->json();
                if (! is_array($items)) {
                    $lastError = 'Respons GitHub tidak valid.';
                    continue;
                }

                $repos = collect($items)
                    ->filter(fn ($repo) => is_array($repo) && empty($repo['private']))
                    ->map(fn (array $repo) => [
                        'name' => (string) ($repo['name'] ?? ''),
                        'full_name' => (string) ($repo['full_name'] ?? ''),
                        'description' => $repo['description'] ?? null,
                        'html_url' => (string) ($repo['html_url'] ?? ''),
                        'language' => $repo['language'] ?? null,
                        'stargazers_count' => (int) ($repo['stargazers_count'] ?? 0),
                        'forks_count' => (int) ($repo['forks_count'] ?? 0),
                        'updated_at' => $repo['updated_at'] ?? null,
                        'topics' => is_array($repo['topics'] ?? null) ? $repo['topics'] : [],
                    ])
                    ->filter(fn ($repo) => $repo['name'] !== '' && $repo['html_url'] !== '')
                    ->values()
                    ->all();

                return [
                    'login' => $login,
                    'repos' => $repos,
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                Log::warning('Gagal mengambil repo GitHub', [
                    'login' => $login,
                    'endpoint' => $endpoint,
                    'message' => $e->getMessage(),
                ]);
                $lastError = 'Tidak bisa menghubungi GitHub saat ini.';
            }
        }

        return [
            'login' => $login,
            'repos' => [],
            'error' => $lastError ?: 'Akun/organisasi GitHub tidak ditemukan.',
        ];
    }
}
