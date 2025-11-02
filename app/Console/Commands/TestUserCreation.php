<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\RolePermission;

class TestUserCreation extends Command
{
    protected $signature = 'test:user-creation {email=test+auto@example.com}';
    protected $description = 'Créer un utilisateur et une entreprise de test, lancer le bootstrap et vérifier role_permissions';

    public function handle(): int
    {
        $email = $this->argument('email');
        $suffix = substr(uniqid(), -6);

        // Nettoyage éventuel
        User::where('email', $email)->delete();

        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Auto',
            'email' => $email,
            'mobile' => '0700' . $suffix,
            'password' => Hash::make('Password123!'),
            'status' => 'active',
            'role' => 'admin',
            'user_type' => 'entreprise_admin'
        ]);

        $this->info("Utilisateur créé: ID={$user->id}");

        $entreprise = Entreprise::create([
            'name' => 'Entreprise Test Auto',
            'mobile' => '0701' . $suffix,
            'email' => $email,
            'adresse' => 'Adresse de test',
            'commune_id' => 1,
            'statut' => 1,
            'created_by' => $user->id
        ]);

        $user->update(['entreprise_id' => $entreprise->id]);
        $this->info("Entreprise créée: ID={$entreprise->id}");

        // Lancer le bootstrap
        app(\App\Services\TenantBootstrapService::class)->bootstrapEntreprise($entreprise->id, $user->id);

        // Vérifier role_permissions
        $count = RolePermission::where('entreprise_id', $entreprise->id)->count();
        $this->info("role_permissions count for entreprise {$entreprise->id}: {$count}");

        $roles = RolePermission::where('entreprise_id', $entreprise->id)->pluck('permissions','role')->toArray();
        $this->line(json_encode($roles, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}


