<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte d'unicité sur 'role' seulement
            $table->dropUnique('role_permissions_role_unique');

            // Ajouter une nouvelle contrainte d'unicité composite sur 'role' et 'entreprise_id'
            $table->unique(['role', 'entreprise_id'], 'role_permissions_role_entreprise_unique');
        });
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
