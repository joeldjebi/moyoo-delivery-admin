<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Temp;
use Carbon\Carbon;

class TempSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $temps = [
            [
                'libelle' => 'Jour (6h-18h)',
                'description' => 'Période de jour standard',
                'heure_debut' => '06:00:00',
                'heure_fin' => '18:00:00',
                'is_weekend' => false,
                'is_holiday' => false,
                'is_active' => true,
                'created_by' => 1
            ],
            [
                'libelle' => 'Nuit (18h-6h)',
                'description' => 'Période de nuit',
                'heure_debut' => '18:00:00',
                'heure_fin' => '06:00:00',
                'is_weekend' => false,
                'is_holiday' => false,
                'is_active' => true,
                'created_by' => 1
            ],
            [
                'libelle' => 'Week-end',
                'description' => 'Samedi et dimanche',
                'heure_debut' => null,
                'heure_fin' => null,
                'is_weekend' => true,
                'is_holiday' => false,
                'is_active' => true,
                'created_by' => 1
            ],
            [
                'libelle' => 'Jours fériés',
                'description' => 'Jours fériés nationaux',
                'heure_debut' => null,
                'heure_fin' => null,
                'is_weekend' => false,
                'is_holiday' => true,
                'is_active' => true,
                'created_by' => 1
            ],
            [
                'libelle' => 'Heures de pointe (7h-9h)',
                'description' => 'Heures de pointe matinales',
                'heure_debut' => '07:00:00',
                'heure_fin' => '09:00:00',
                'is_weekend' => false,
                'is_holiday' => false,
                'is_active' => true,
                'created_by' => 1
            ],
            [
                'libelle' => 'Heures de pointe (17h-19h)',
                'description' => 'Heures de pointe du soir',
                'heure_debut' => '17:00:00',
                'heure_fin' => '19:00:00',
                'is_weekend' => false,
                'is_holiday' => false,
                'is_active' => true,
                'created_by' => 1
            ]
        ];

        foreach ($temps as $temp) {
            Temp::create($temp);
        }
    }
}
