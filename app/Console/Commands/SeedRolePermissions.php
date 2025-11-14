<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entreprise;
use App\Models\RolePermission;

class SeedRolePermissions extends Command
{
    protected $signature = 'seed:role-permissions {--entreprise_id=}';
    protected $description = 'Peupler la table role_permissions pour toutes les entreprises ou une entreprise spécifique';

    public function handle(): int
    {
        $entrepriseId = $this->option('entreprise_id');
        $entreprises = $entrepriseId ? Entreprise::where('id', $entrepriseId)->get() : Entreprise::all();

        if ($entreprises->isEmpty()) {
            $this->warn('Aucune entreprise trouvée.');
            return self::SUCCESS;
        }

        $map = [
            'admin' => [
                'users.create','users.read','users.update','users.delete',
                'colis.create','colis.read','colis.update','colis.delete',
                'livreurs.create','livreurs.read','livreurs.update','livreurs.delete',
                'marchands.create','marchands.read','marchands.update','marchands.delete',
                'reversements.create','reversements.read','reversements.update',
                'reports.read','settings.read','settings.update'
            ],
            'manager' => [
                'colis.create','colis.read','colis.update','colis.delete',
                'livreurs.create','livreurs.read','livreurs.update','livreurs.delete',
                'marchands.create','marchands.read','marchands.update','marchands.delete',
                'reports.read'
            ],
            'user' => [
                'colis.create','colis.read','livreurs.read','marchands.read'
            ],
        ];

        foreach ($entreprises as $entreprise) {
            foreach ($map as $role => $permissions) {
                RolePermission::updateOrCreate(
                    ['role' => $role, 'entreprise_id' => $entreprise->id],
                    ['permissions' => $permissions]
                );
            }
            $this->info("Permissions seedées pour l'entreprise ID {$entreprise->id}");
        }

        return self::SUCCESS;
    }
}
