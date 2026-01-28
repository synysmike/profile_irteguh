<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\Contributor;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCaseStudies = CaseStudy::where('featured', true)
            ->orderBy('order')
            ->limit(6)
            ->get();
        
        $categories = CaseStudy::select('category')
            ->distinct()
            ->pluck('category');
        
        $contributors = Contributor::active()->ordered()->get();
        
        return view('public.home', compact('featuredCaseStudies', 'categories', 'contributors'));
    }
}
