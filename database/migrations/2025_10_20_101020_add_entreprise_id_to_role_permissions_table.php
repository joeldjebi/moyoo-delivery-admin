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

		if (!Schema::hasColumn('role_permissions', 'entreprise_id')) {
			// Certains environnements ont une table `role_permissions` au format pivot
			// (role_id, permission_id) => pas de colonne `role`.
			$after = Schema::hasColumn('role_permissions', 'role')
				? 'role'
				: (Schema::hasColumn('role_permissions', 'permission_id')
					? 'permission_id'
					: (Schema::hasColumn('role_permissions', 'role_id') ? 'role_id' : 'id'));

			Schema::table('role_permissions', function (Blueprint $table) use ($after) {
				// `entreprises.id` est `$table->id()` => unsignedBigInteger (sinon FK 1215)
				$col = $table->unsignedBigInteger('entreprise_id')->nullable();
				if ($after) {
					$col->after($after);
				}
			});

			// Assurer l'alignement du type côté MySQL même si DBAL n'est pas installé.
			try {
				DB::statement('ALTER TABLE `role_permissions` MODIFY `entreprise_id` BIGINT UNSIGNED NULL');
			} catch (\Throwable $e) {
			}

			// Ajouter la clé étrangère et l'index si la table entreprises existe
			if (Schema::hasTable('entreprises')) {
				Schema::table('role_permissions', function (Blueprint $table) {
					try {
						$table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
					} catch (\Throwable $e) {}
					try {
						$table->index('entreprise_id');
					} catch (\Throwable $e) {}
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
			try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
			try { $table->dropIndex(['entreprise_id']); } catch (\Throwable $e) {}
			if (Schema::hasColumn('role_permissions', 'entreprise_id')) {
				$table->dropColumn('entreprise_id');
			}
		});
	}
};
