<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conditionnement_colis')) return;
        Schema::table('conditionnement_colis', function (Blueprint $table) {
            if (!Schema::hasColumn('conditionnement_colis', 'entreprise_id')) {
                $table->bigInteger('entreprise_id')->after('id');
            }
            if (!Schema::hasColumn('conditionnement_colis', 'libelle')) {
                $table->string('libelle', 200)->after('entreprise_id');
            }
            if (!Schema::hasColumn('conditionnement_colis', 'created_by')) {
                $table->integer('created_by')->after('libelle');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('conditionnement_colis')) return;
        Schema::table('conditionnement_colis', function (Blueprint $table) {
            if (Schema::hasColumn('conditionnement_colis', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('conditionnement_colis', 'libelle')) {
                $table->dropColumn('libelle');
            }
            if (Schema::hasColumn('conditionnement_colis', 'entreprise_id')) {
                $table->dropColumn('entreprise_id');
            }
        });
    }
};


