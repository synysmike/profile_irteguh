<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use App\Models\HeroText;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Slide;

class HomeController extends Controller
{
    public function index()
    {
        $theme = Setting::homeTheme();

        $heroTexts = HeroText::active()->ordered()->pluck('text')->toArray();
        if (empty($heroTexts)) {
            $heroTexts = ['Solusi IT & Kreatif Terintegrasi'];
        }

        $featuredProjects = collect();
        if ($theme['show_featured']) {
            $featuredProjects = Project::portfolioPublic()
                ->with('portfolioMedia')
                ->where('is_portfolio_featured', true)
                ->portfolioOrdered()
                ->limit(6)
                ->get();
        }

        $categories = collect();
        if ($theme['show_categories']) {
            $categories = Project::portfolioPublic()
                ->whereNotNull('portfolio_category')
                ->distinct()
                ->orderBy('portfolio_category')
                ->pluck('portfolio_category');

            if ($categories->isEmpty()) {
                $categories = collect(Project::portfolioCategories());
            }
        }

        $contributors = collect();
        if ($theme['show_team']) {
            $contributors = Contributor::active()->ordered()->with('githubTeam')->get();
        }

        $previewServices = collect();
        if ($theme['show_services']) {
            $previewServices = Service::active()->ordered()->limit(8)->get();
        }

        $slides = Slide::active()->ordered()->get();

        return view('public.home', compact(
            'theme',
            'heroTexts',
            'featuredProjects',
            'categories',
            'contributors',
            'previewServices',
            'slides'
        ));
    }
}
