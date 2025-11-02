<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('villes') || !Schema::hasTable('communes')) {
            return; // Sécurité: tables non prêtes
        }

        // Créer la ville Abidjan si elle n'existe pas
        $villeId = DB::table('villes')->where('libelle', 'Abidjan')->value('id');
        if (!$villeId) {
            $villeId = DB::table('villes')->insertGetId([
                'libelle' => 'Abidjan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Communes d'Abidjan
        $communes = [
            'Abobo','Adjamé','Anyama','Attécoubé','Bingerville','Cocody','Koumassi','Marcory','Plateau','Port-Bouët','Treichville','Songon','Yopougon'
        ];

        foreach ($communes as $libelle) {
            $exists = DB::table('communes')
                ->where('libelle', $libelle)
                ->where('ville_id', $villeId)
                ->exists();
            if (!$exists) {
                DB::table('communes')->insert([
                    'libelle' => $libelle,
                    'ville_id' => $villeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('villes') || !Schema::hasTable('communes')) {
            return;
        }
        $villeId = DB::table('villes')->where('libelle', 'Abidjan')->value('id');
        if ($villeId) {
            // Supprimer uniquement les communes insérées par cette migration
            DB::table('communes')->where('ville_id', $villeId)
                ->whereIn('libelle', [
                    'Abobo','Adjamé','Anyama','Attécoubé','Bingerville','Cocody','Koumassi','Marcory','Plateau','Port-Bouët','Treichville','Songon','Yopougon'
                ])->delete();
        }
        // Ne pas supprimer la ville pour éviter d'impacter d'autres données
    }
};


