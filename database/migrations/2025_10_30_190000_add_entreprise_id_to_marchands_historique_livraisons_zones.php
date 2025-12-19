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
        // `entreprises.id` est `$table->id()` => BIGINT UNSIGNED.
        // Pour éviter les erreurs MySQL 1215, on crée/alimente `entreprise_id` en unsigned,
        // on aligne le type (même sans DBAL), on nettoie les valeurs orphelines,
        // puis on tente FK + index en mode "safe".

        // Ajouter entreprise_id à la table marchands
        if (Schema::hasTable('marchands')) {
            if (!Schema::hasColumn('marchands', 'entreprise_id')) {
                Schema::table('marchands', function (Blueprint $table) {
                    $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');
                });
            }

            try { DB::statement('ALTER TABLE `marchands` MODIFY `entreprise_id` BIGINT UNSIGNED NULL'); } catch (\Throwable $e) {}
            try {
                DB::statement('
                    UPDATE `marchands` m
                    LEFT JOIN `entreprises` e ON m.`entreprise_id` = e.`id`
                    SET m.`entreprise_id` = NULL
                    WHERE m.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
                ');
            } catch (\Throwable $e) {}

            if (Schema::hasTable('entreprises') && Schema::hasColumn('marchands', 'entreprise_id')) {
                Schema::table('marchands', function (Blueprint $table) {
                    try {
                        $table->foreign('entreprise_id')
                            ->references('id')
                            ->on('entreprises')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                    } catch (\Throwable $e) {}
                    try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
                });
            }
        }

        // Ajouter entreprise_id à la table historique_livraisons
        if (Schema::hasTable('historique_livraisons')) {
            if (!Schema::hasColumn('historique_livraisons', 'entreprise_id')) {
                Schema::table('historique_livraisons', function (Blueprint $table) {
                    $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');
                });
            }

            try { DB::statement('ALTER TABLE `historique_livraisons` MODIFY `entreprise_id` BIGINT UNSIGNED NULL'); } catch (\Throwable $e) {}
            try {
                DB::statement('
                    UPDATE `historique_livraisons` hl
                    LEFT JOIN `entreprises` e ON hl.`entreprise_id` = e.`id`
                    SET hl.`entreprise_id` = NULL
                    WHERE hl.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
                ');
            } catch (\Throwable $e) {}

            if (Schema::hasTable('entreprises') && Schema::hasColumn('historique_livraisons', 'entreprise_id')) {
                Schema::table('historique_livraisons', function (Blueprint $table) {
                    try {
                        $table->foreign('entreprise_id')
                            ->references('id')
                            ->on('entreprises')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                    } catch (\Throwable $e) {}
                    try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
                });
            }
        }

        // Ajouter entreprise_id à la table zones
        if (Schema::hasTable('zones')) {
            if (!Schema::hasColumn('zones', 'entreprise_id')) {
                Schema::table('zones', function (Blueprint $table) {
                    $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');
                });
            }

            try { DB::statement('ALTER TABLE `zones` MODIFY `entreprise_id` BIGINT UNSIGNED NULL'); } catch (\Throwable $e) {}
            try {
                DB::statement('
                    UPDATE `zones` z
                    LEFT JOIN `entreprises` e ON z.`entreprise_id` = e.`id`
                    SET z.`entreprise_id` = NULL
                    WHERE z.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
                ');
            } catch (\Throwable $e) {}

            if (Schema::hasTable('entreprises') && Schema::hasColumn('zones', 'entreprise_id')) {
                Schema::table('zones', function (Blueprint $table) {
                    try {
                        $table->foreign('entreprise_id')
                            ->references('id')
                            ->on('entreprises')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                    } catch (\Throwable $e) {}
                    try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
                });
            }
        }

        // Ajouter entreprise_id à la table livreurs
        if (Schema::hasTable('livreurs')) {
            if (!Schema::hasColumn('livreurs', 'entreprise_id')) {
                Schema::table('livreurs', function (Blueprint $table) {
                    $table->unsignedBigInteger('entreprise_id')->nullable()->after('id');
                });
            }

            try { DB::statement('ALTER TABLE `livreurs` MODIFY `entreprise_id` BIGINT UNSIGNED NULL'); } catch (\Throwable $e) {}
            try {
                DB::statement('
                    UPDATE `livreurs` l
                    LEFT JOIN `entreprises` e ON l.`entreprise_id` = e.`id`
                    SET l.`entreprise_id` = NULL
                    WHERE l.`entreprise_id` IS NOT NULL AND e.`id` IS NULL
                ');
            } catch (\Throwable $e) {}

            if (Schema::hasTable('entreprises') && Schema::hasColumn('livreurs', 'entreprise_id')) {
                Schema::table('livreurs', function (Blueprint $table) {
                    try {
                        $table->foreign('entreprise_id')
                            ->references('id')
                            ->on('entreprises')
                            ->onDelete('cascade')
                            ->onUpdate('cascade');
                    } catch (\Throwable $e) {}
                    try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
                });
            }
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
                    try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
                    $table->dropColumn('entreprise_id');
                }
            });
        }

        // Supprimer entreprise_id de la table historique_livraisons
        if (Schema::hasTable('historique_livraisons')) {
            Schema::table('historique_livraisons', function (Blueprint $table) {
                if (Schema::hasColumn('historique_livraisons', 'entreprise_id')) {
                    try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
                    $table->dropColumn('entreprise_id');
                }
            });
        }

        // Supprimer entreprise_id de la table marchands
        if (Schema::hasTable('marchands')) {
            Schema::table('marchands', function (Blueprint $table) {
                if (Schema::hasColumn('marchands', 'entreprise_id')) {
                    try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
                    $table->dropColumn('entreprise_id');
                }
            });
        }

        // Supprimer entreprise_id de la table livreurs
        if (Schema::hasTable('livreurs')) {
            Schema::table('livreurs', function (Blueprint $table) {
                if (Schema::hasColumn('livreurs', 'entreprise_id')) {
                    try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
                    $table->dropColumn('entreprise_id');
                }
            });
        }
    }
};
