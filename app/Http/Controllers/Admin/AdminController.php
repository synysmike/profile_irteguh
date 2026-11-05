<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Slide;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $stats = [
            'portfolio_public' => Project::portfolioPublic()->count(),
            'slides' => Slide::count(),
            'featured_projects' => Project::portfolioPublic()->where('is_portfolio_featured', true)->count(),
        ];

        $recentPortfolio = Project::portfolioPublic()
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPortfolio'));
    }
}
