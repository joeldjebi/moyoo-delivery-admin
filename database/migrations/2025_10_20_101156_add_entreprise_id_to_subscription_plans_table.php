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
		Schema::table('subscription_plans', function (Blueprint $table) {
			if (!Schema::hasColumn('subscription_plans', 'entreprise_id')) {
				$table->bigInteger('entreprise_id')->nullable()->after('slug');
			}
		});

		// Vérifier et ajouter la foreign key si elle n'existe pas
		if (Schema::hasColumn('subscription_plans', 'entreprise_id')) {
			$constraintExists = DB::selectOne("
				SELECT 1
				FROM pg_constraint
				WHERE conrelid = 'subscription_plans'::regclass
				AND contype = 'f'
				AND conname = 'subscription_plans_entreprise_id_foreign'
			");

			if (!$constraintExists) {
				Schema::table('subscription_plans', function (Blueprint $table) {
					$table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
				});
			}

			// Vérifier si l'index existe
			$indexExists = DB::selectOne("
				SELECT 1
				FROM pg_indexes
				WHERE tablename = 'subscription_plans'
				AND indexname = 'subscription_plans_entreprise_id_index'
			");

			if (!$indexExists) {
				Schema::table('subscription_plans', function (Blueprint $table) {
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
		Schema::table('subscription_plans', function (Blueprint $table) {
			$table->dropForeign(['entreprise_id']);
			$table->dropIndex(['entreprise_id']);
			$table->dropColumn('entreprise_id');
		});
	}
};
