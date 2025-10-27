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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->string('type'); // depot, retrait, virement, paiement, etc.
            $table->decimal('montant', 15, 2);
            $table->string('devise', 3)->default('EUR');
            $table->text('description')->nullable();
            $table->string('statut')->default('effectuee'); // effectuee, annulee, en_cours
            $table->foreignUuid('compte_id')->constrained('comptes')->onDelete('cascade');
            $table->foreignUuid('compte_destination_id')->nullable()->constrained('comptes')->onDelete('set null');
            $table->timestamp('date_transaction');
            $table->json('metadata')->nullable(); // pour stocker des données supplémentaires
            $table->timestamps();

            // Indexes
            $table->index(['compte_id', 'date_transaction']);
            $table->index(['compte_destination_id', 'date_transaction']);
            $table->index('reference');
            $table->index('type');
            $table->index('statut');
            $table->index('date_transaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
