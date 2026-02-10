<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServicesController extends Controller
{
    public function index()
    {
        $services = Service::active()->ordered()->get();
        return view('public.services', compact('services'));
    }
}
