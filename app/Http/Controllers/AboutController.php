<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $contributors = Contributor::active()->ordered()->with('githubTeam')->get();
        return view('public.about', compact('contributors'));
    }
}
