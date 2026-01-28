<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CaseStudy;
use App\Models\Slide;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $stats = [
            'case_studies' => CaseStudy::count(),
            'slides' => Slide::count(),
            'featured_projects' => CaseStudy::where('featured', true)->count(),
        ];

        $recentCaseStudies = CaseStudy::latest()->limit(5)->get();
        
        return view('admin.dashboard', compact('stats', 'recentCaseStudies'));
    }
}
