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
		if (!Schema::hasTable('role_permissions')) {
			return;
		}

		if (!Schema::hasColumn('role_permissions', 'entreprise_id')) {
			Schema::table('role_permissions', function (Blueprint $table) {
				$table->bigInteger('entreprise_id')->nullable()->after('role');
			});

			// Ajouter la clé étrangère et l'index si la table entreprises existe
			if (Schema::hasTable('entreprises')) {
				Schema::table('role_permissions', function (Blueprint $table) {
					$table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
					$table->index('entreprise_id');
				});
			}
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('role_permissions', function (Blueprint $table) {
			$table->dropForeign(['entreprise_id']);
			$table->dropIndex(['entreprise_id']);
			$table->dropColumn('entreprise_id');
		});
	}
};
