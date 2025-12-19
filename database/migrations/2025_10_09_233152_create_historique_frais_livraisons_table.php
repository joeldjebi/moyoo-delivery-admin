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
        if (Schema::hasTable('historique_frais_livraisons')) {
            return;
        }

        Schema::create('historique_frais_livraisons', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('frais_livraison_id');
            $table->bigInteger('colis_id')->nullable();
            $table->bigInteger('livraison_id')->nullable();
            $table->string('type_operation'); // 'creation', 'modification', 'suppression', 'application'
            $table->decimal('montant_avant', 10, 2)->nullable();
            $table->decimal('montant_apres', 10, 2)->nullable();
            $table->text('description_operation');
            $table->json('donnees_avant')->nullable();
            $table->json('donnees_apres')->nullable();
            $table->bigInteger('entreprise_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('date_operation');
            $table->timestamps();

            // FKs: éviter de faire échouer le migrate si les tables cibles / contraintes existent déjà
            // ou si l'environnement a un ordre de migration différent.
            try { $table->foreign('frais_livraison_id')->references('id')->on('frais_livraisons')->onDelete('cascade'); } catch (\Throwable $e) {}
            try { $table->foreign('colis_id')->references('id')->on('colis')->onDelete('set null'); } catch (\Throwable $e) {}
            try { $table->foreign('livraison_id')->references('id')->on('historique_livraisons')->onDelete('set null'); } catch (\Throwable $e) {}
            try { $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade'); } catch (\Throwable $e) {}
            try { $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); } catch (\Throwable $e) {}

            $table->index(['frais_livraison_id', 'date_operation']);
            $table->index(['entreprise_id', 'type_operation']);
            $table->index(['colis_id', 'livraison_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_frais_livraisons');
    }
};
