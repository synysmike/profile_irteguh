<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    public function show($slug)
    {
        $caseStudy = CaseStudy::where('slug', $slug)->firstOrFail();
        
        // Get related case studies
        $related = CaseStudy::where('category', $caseStudy->category)
            ->where('id', '!=', $caseStudy->id)
            ->limit(3)
            ->get();
        
        return view('public.case-study', compact('caseStudy', 'related'));
    }
}
