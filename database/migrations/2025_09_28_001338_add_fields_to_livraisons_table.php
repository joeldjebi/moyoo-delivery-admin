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
        Schema::table('livraisons', function (Blueprint $table) {
            $table->unsignedBigInteger('package_colis_id')->nullable()->after('colis_id');
            $table->unsignedBigInteger('marchand_id')->nullable()->after('package_colis_id');
            $table->unsignedBigInteger('boutique_id')->nullable()->after('marchand_id');

            // Ajouter les clés étrangères
            $table->foreign('package_colis_id')->references('id')->on('package_colis')->onDelete('cascade');
            $table->foreign('marchand_id')->references('id')->on('marchands')->onDelete('cascade');
            $table->foreign('boutique_id')->references('id')->on('boutiques')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livraisons', function (Blueprint $table) {
            $table->dropForeign(['package_colis_id']);
            $table->dropForeign(['marchand_id']);
            $table->dropForeign(['boutique_id']);

            $table->dropColumn(['package_colis_id', 'marchand_id', 'boutique_id']);
        });
    }
};
