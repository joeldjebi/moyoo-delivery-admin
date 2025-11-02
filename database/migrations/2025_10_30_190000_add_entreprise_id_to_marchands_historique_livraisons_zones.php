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
        // Ajouter entreprise_id à la table marchands
        if (Schema::hasTable('marchands')) {
            Schema::table('marchands', function (Blueprint $table) {
                if (!Schema::hasColumn('marchands', 'entreprise_id')) {
                    $table->bigInteger('entreprise_id')->nullable()->after('id');
                    $table->foreign('entreprise_id')
                          ->references('id')
                          ->on('entreprises')
                          ->onDelete('cascade')
                          ->onUpdate('cascade');
                }
            });
        }

        // Ajouter entreprise_id à la table historique_livraisons
        if (Schema::hasTable('historique_livraisons')) {
            Schema::table('historique_livraisons', function (Blueprint $table) {
                if (!Schema::hasColumn('historique_livraisons', 'entreprise_id')) {
                    $table->bigInteger('entreprise_id')->nullable()->after('id');
                    $table->foreign('entreprise_id')
                          ->references('id')
                          ->on('entreprises')
                          ->onDelete('cascade')
                          ->onUpdate('cascade');
                }
            });
        }

        // Ajouter entreprise_id à la table zones
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
                if (!Schema::hasColumn('zones', 'entreprise_id')) {
                    $table->bigInteger('entreprise_id')->nullable()->after('id');
                    $table->foreign('entreprise_id')
                          ->references('id')
                          ->on('entreprises')
                          ->onDelete('cascade')
                          ->onUpdate('cascade');
                }
            });
        }

        // Ajouter entreprise_id à la table livreurs
        if (Schema::hasTable('livreurs')) {
            Schema::table('livreurs', function (Blueprint $table) {
                if (!Schema::hasColumn('livreurs', 'entreprise_id')) {
                    $table->bigInteger('entreprise_id')->nullable()->after('id');
                    $table->foreign('entreprise_id')
                          ->references('id')
                          ->on('entreprises')
                          ->onDelete('cascade')
                          ->onUpdate('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer entreprise_id de la table zones
        if (Schema::hasTable('zones')) {
            Schema::table('zones', function (Blueprint $table) {
                if (Schema::hasColumn('zones', 'entreprise_id')) {
                    $table->dropForeign(['entreprise_id']);
                    $table->dropColumn('entreprise_id');
                }
            });
        }

        // Supprimer entreprise_id de la table historique_livraisons
        if (Schema::hasTable('historique_livraisons')) {
            Schema::table('historique_livraisons', function (Blueprint $table) {
                if (Schema::hasColumn('historique_livraisons', 'entreprise_id')) {
                    $table->dropForeign(['entreprise_id']);
                    $table->dropColumn('entreprise_id');
                }
            });
        }

        // Supprimer entreprise_id de la table marchands
        if (Schema::hasTable('marchands')) {
            Schema::table('marchands', function (Blueprint $table) {
                if (Schema::hasColumn('marchands', 'entreprise_id')) {
                    $table->dropForeign(['entreprise_id']);
                    $table->dropColumn('entreprise_id');
                }
            });
        }

        // Supprimer entreprise_id de la table livreurs
        if (Schema::hasTable('livreurs')) {
            Schema::table('livreurs', function (Blueprint $table) {
                if (Schema::hasColumn('livreurs', 'entreprise_id')) {
                    $table->dropForeign(['entreprise_id']);
                    $table->dropColumn('entreprise_id');
                }
            });
        }
    }
};
