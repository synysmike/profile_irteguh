<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Models\HeroText;
use App\Models\Project;
use App\Models\Slide;

class HomeController extends Controller
{
    public function index()
    {
        $heroTexts = HeroText::active()->ordered()->pluck('text')->toArray();
        if (empty($heroTexts)) {
            $heroTexts = ['Solusi IT & Kreatif Terintegrasi'];
        }

        $featuredProjects = Project::portfolioPublic()
            ->with('portfolioMedia')
            ->where('is_portfolio_featured', true)
            ->portfolioOrdered()
            ->limit(6)
            ->get();

        $categories = Project::portfolioPublic()
            ->whereNotNull('portfolio_category')
            ->distinct()
            ->orderBy('portfolio_category')
            ->pluck('portfolio_category');

        if ($categories->isEmpty()) {
            $categories = collect(Project::portfolioCategories());
        }

        $contributors = Contributor::active()->ordered()->with('githubTeam')->get();
        $slides = Slide::active()->ordered()->get();

        return view('public.home', compact('heroTexts', 'featuredProjects', 'categories', 'contributors', 'slides'));
    }
}
