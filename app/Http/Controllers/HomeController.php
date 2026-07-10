<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\Contributor;
use App\Models\HeroText;
use App\Models\Slide;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $heroTexts = HeroText::active()->ordered()->pluck('text')->toArray();
        if (empty($heroTexts)) {
            $heroTexts = ['Solusi IT & Kreatif Terintegrasi'];
        }

        $featuredCaseStudies = CaseStudy::where('featured', true)
            ->orderBy('order')
            ->limit(6)
            ->get();
        
        $categories = CaseStudy::select('category')
            ->distinct()
            ->pluck('category');
        
        $contributors = Contributor::active()->ordered()->get();

        $slides = Slide::active()->ordered()->get();
        
        return view('public.home', compact('heroTexts', 'featuredCaseStudies', 'categories', 'contributors', 'slides'));
    }
}
