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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero')->unique();
            $table->string('type'); // courant, epargne, etc.
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise', 3)->default('EUR');
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->timestamp('date_ouverture')->useCurrent();
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['client_id', 'is_active']);
            $table->index('numero');
            $table->index('type');
            $table->index('last_transaction_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
