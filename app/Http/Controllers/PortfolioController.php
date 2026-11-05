<?php

namespace App\Http\Controllers;

use App\Models\GithubTeam;
use App\Models\Project;
use App\Services\GithubRepoService;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request, GithubRepoService $githubRepos)
    {
        $query = Project::portfolioPublic()->portfolioOrdered();

        if ($request->filled('category')) {
            $query->where('portfolio_category', $request->category);
        }

        if ($request->filled('year')) {
            $query->where('portfolio_year', (int) $request->year);
        }

        if ($request->filled('tag')) {
            $query->whereJsonContains('portfolio_tags', $request->tag);
        }

        $projects = $query->with('portfolioMedia')->get();

        $categories = Project::portfolioPublic()
            ->whereNotNull('portfolio_category')
            ->distinct()
            ->orderBy('portfolio_category')
            ->pluck('portfolio_category');

        $years = Project::portfolioPublic()
            ->whereNotNull('portfolio_year')
            ->distinct()
            ->orderByDesc('portfolio_year')
            ->pluck('portfolio_year');

        $githubTeams = GithubTeam::active()
            ->ordered()
            ->with(['activeContributors' => fn ($q) => $q->ordered()])
            ->get();
        $githubTeamRepos = $githubRepos->reposForTeams($githubTeams);

        return view('public.portfolio', compact('projects', 'categories', 'years', 'githubTeamRepos'));
    }

    public function show(string $slug)
    {
        $project = Project::portfolioPublic()
            ->with(['portfolioMedia', 'publishedReviews.media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Project::portfolioPublic()
            ->with(['portfolioMedia', 'publishedReviews'])
            ->withAvg('publishedReviews', 'rating')
            ->withCount('publishedReviews')
            ->where('id', '!=', $project->id)
            ->when($project->portfolio_category, fn ($q) => $q->where('portfolio_category', $project->portfolio_category))
            ->portfolioOrdered()
            ->limit(3)
            ->get();

        return view('public.portfolio-show', compact('project', 'related'));
    }
}
