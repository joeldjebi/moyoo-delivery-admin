<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        // Vérifier que les colonnes nécessaires existent
        $hasRole = Schema::hasColumn('role_permissions', 'role');
        $hasEntrepriseId = Schema::hasColumn('role_permissions', 'entreprise_id');

        if (!$hasRole || !$hasEntrepriseId) {
            \Log::warning('Migration fix_role_permissions_unique_constraint: Colonnes manquantes', [
                'has_role' => $hasRole,
                'has_entreprise_id' => $hasEntrepriseId
            ]);
            return;
        }

        // Vérifier si la contrainte composite existe déjà
        $constraintExists = DB::select("
            SELECT constraint_name 
            FROM information_schema.table_constraints 
            WHERE table_name = 'role_permissions' 
            AND constraint_name = 'role_permissions_role_entreprise_unique'
        ");

        if (empty($constraintExists)) {
            // Vérifier si l'ancienne contrainte existe avant de la supprimer
            $oldConstraintExists = DB::select("
                SELECT constraint_name 
                FROM information_schema.table_constraints 
                WHERE table_name = 'role_permissions' 
                AND constraint_name = 'role_permissions_role_unique'
            ");

            if (!empty($oldConstraintExists)) {
                // Supprimer l'ancienne contrainte d'unicité sur 'role' seulement
                DB::statement('ALTER TABLE role_permissions DROP CONSTRAINT IF EXISTS role_permissions_role_unique');
            }

            // Ajouter une nouvelle contrainte d'unicité composite sur 'role' et 'entreprise_id'
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->unique(['role', 'entreprise_id'], 'role_permissions_role_entreprise_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            // Supprimer la contrainte composite
            $table->dropUnique('role_permissions_role_entreprise_unique');

            // Restaurer l'ancienne contrainte d'unicité sur 'role' seulement
            $table->unique('role', 'role_permissions_role_unique');
        });
    }
};
