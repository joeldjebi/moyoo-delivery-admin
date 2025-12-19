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
        // Migration rejouable : si la table existe déjà (DB partiellement migrée / import),
        // on ne tente pas de la recréer.
        if (Schema::hasTable('frais_livraisons')) {
            return;
        }

        Schema::create('frais_livraisons', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->decimal('montant', 10, 2);
            $table->string('type_frais'); // 'fixe', 'pourcentage', 'par_km', 'par_colis'
            $table->string('zone_applicable')->nullable(); // 'toutes', 'urbain', 'rural', 'specifique'
            $table->json('zones_specifiques')->nullable(); // Pour les zones spécifiques
            $table->boolean('actif')->default(true);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            // `entreprises.id` est `$table->id()` => unsignedBigInteger
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            // FKs: si les tables cibles n'existent pas encore dans certains environnements,
            // on évite de faire échouer le migrate.
            try {
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            } catch (\Throwable $e) {}
            try {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            } catch (\Throwable $e) {}

            $table->index(['entreprise_id', 'actif']);
            $table->index(['type_frais', 'zone_applicable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frais_livraisons');
    }
};
