<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Affiche la landing page publique
     */
    public function index()
    {
        return view('landing.index');
    }
}
