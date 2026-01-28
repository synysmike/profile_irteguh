<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $query = CaseStudy::query();
        
        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->where('year', $request->year);
        }
        
        // Filter by tag
        if ($request->has('tag') && $request->tag) {
            $query->whereJsonContains('tags', $request->tag);
        }
        
        $caseStudies = $query->orderBy('order')->orderBy('year', 'desc')->get();
        
        $categories = CaseStudy::select('category')->distinct()->pluck('category');
        $years = CaseStudy::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $allTags = CaseStudy::whereNotNull('tags')->pluck('tags')->flatten()->unique()->sort();
        
        return view('public.portfolio', compact('caseStudies', 'categories', 'years', 'allTags'));
    }
}
