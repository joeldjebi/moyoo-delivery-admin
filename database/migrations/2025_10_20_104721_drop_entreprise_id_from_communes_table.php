<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('communes', function (Blueprint $table) {
			if (Schema::hasColumn('communes', 'entreprise_id')) {
				// D'abord supprimer l'index si présent (nommé comme un FK dans votre schéma actuel)
				try { $table->dropIndex('communes_entreprise_id_foreign'); } catch (\Throwable $e) {}
				// Puis supprimer la colonne
				$table->dropColumn('entreprise_id');
			}
		});
	}

	public function down(): void
	{
		Schema::table('communes', function (Blueprint $table) {
			$table->bigInteger('entreprise_id')->nullable()->after('id');
			$table->index('entreprise_id');
		});
	}
};
