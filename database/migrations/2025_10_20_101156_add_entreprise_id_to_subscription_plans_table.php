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
		if (!Schema::hasTable('subscription_plans')) {
			return;
		}

		// Migration rejouable + compatible MySQL/PGSQL :
		// - pas de requêtes `pg_constraint` / `pg_indexes` sous MySQL
		// - on tente d'ajouter FK/Index en mode "safe" (try/catch)
		if (!Schema::hasColumn('subscription_plans', 'entreprise_id')) {
			Schema::table('subscription_plans', function (Blueprint $table) {
				// `entreprises.id` est `$table->id()` => unsignedBigInteger (sinon FK 1215)
				$table->unsignedBigInteger('entreprise_id')->nullable()->after('slug');
			});
		}

		// Aligner le type côté MySQL même si DBAL n'est pas installé.
		try {
			DB::statement('ALTER TABLE `subscription_plans` MODIFY `entreprise_id` BIGINT UNSIGNED NULL');
		} catch (\Throwable $e) {
		}

		// Nettoyer les valeurs orphelines avant d'ajouter la FK
		try {
			DB::statement('
				UPDATE `subscription_plans` sp
				LEFT JOIN `entreprises` e ON sp.`entreprise_id` = e.`id`
				SET sp.`entreprise_id` = NULL
				WHERE sp.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
			');
		} catch (\Throwable $e) {
		}

		if (Schema::hasColumn('subscription_plans', 'entreprise_id') && Schema::hasTable('entreprises')) {
			Schema::table('subscription_plans', function (Blueprint $table) {
				try {
					$table->foreign('entreprise_id')
						->references('id')
						->on('entreprises')
						->onDelete('cascade');
				} catch (\Throwable $e) {}

				try {
					$table->index('entreprise_id');
				} catch (\Throwable $e) {}
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('subscription_plans', function (Blueprint $table) {
			try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
			try { $table->dropIndex(['entreprise_id']); } catch (\Throwable $e) {}
			if (Schema::hasColumn('subscription_plans', 'entreprise_id')) {
				$table->dropColumn('entreprise_id');
			}
		});
	}
};
