<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
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

        $projects = $query->get();

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

        return view('public.portfolio', compact('projects', 'categories', 'years'));
    }

    public function show(string $slug)
    {
        $project = Project::portfolioPublic()->where('slug', $slug)->firstOrFail();

        $related = Project::portfolioPublic()
            ->where('id', '!=', $project->id)
            ->when($project->portfolio_category, fn ($q) => $q->where('portfolio_category', $project->portfolio_category))
            ->portfolioOrdered()
            ->limit(3)
            ->get();

        return view('public.portfolio-show', compact('project', 'related'));
    }
}
