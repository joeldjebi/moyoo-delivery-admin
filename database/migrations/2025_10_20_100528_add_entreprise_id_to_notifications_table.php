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
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('notifications', 'entreprise_id')) {
                    $table->bigInteger('entreprise_id')->nullable()->after('notifiable_id');
                }
                try { $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade'); } catch (\Throwable $e) {}
                try { $table->index('entreprise_id'); } catch (\Throwable $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                try { $table->dropForeign(['entreprise_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['entreprise_id']); } catch (\Throwable $e) {}
                if (Schema::hasColumn('notifications', 'entreprise_id')) {
                    $table->dropColumn('entreprise_id');
                }
            });
        }
    }
};
