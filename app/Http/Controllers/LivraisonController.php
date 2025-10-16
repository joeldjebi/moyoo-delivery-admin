<?php

namespace App\Http\Controllers;

use App\Models\Livraison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivraisonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Livraison $livraison)
    {
        $data['menu'] = 'livraisons';
        $data['title'] = 'Détails de la Livraison';

        $data['user'] = Auth::user();
        if(empty($data['user'])){
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Veuillez vous connecter pour accéder à cette page.']);
        }

        // Charger les relations nécessaires
        $livraison->load([
            'entreprise',
            'colis.marchand',
            'colis.boutique',
            'colis.livreur',
            'colis.engin',
            'colis.commune',
            'colis.zone',
            'colis.poids',
            'colis.modeLivraison',
            'colis.temp',
            'colis.conditionnementColis',
            'colis.packageColis',
            'user'
        ]);

        return view('livraisons.show', compact('livraison'))->with('menu', 'livraisons');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Livraison $livraison)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Livraison $livraison)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Livraison $livraison)
    {
        //
    }
}
