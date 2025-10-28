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
        Schema::table('comptes', function (Blueprint $table) {
            $table->timestamp('date_debut_blocage')->nullable();
            $table->timestamp('date_fin_blocage')->nullable();
            $table->string('motif_blocage')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn(['date_debut_blocage', 'date_fin_blocage', 'motif_blocage', 'is_archived', 'archived_at']);
        });
    }
};
