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
        // Use raw SQL for PostgreSQL UUID conversion
        DB::statement('ALTER TABLE users DROP CONSTRAINT users_pkey');
        DB::statement('ALTER TABLE users DROP COLUMN id');
        DB::statement('ALTER TABLE users ADD COLUMN id UUID PRIMARY KEY DEFAULT gen_random_uuid()');

        Schema::table('users', function (Blueprint $table) {
            // Add new fields
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('role')->default('user'); // admin, manager, user
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->json('preferences')->nullable();

            // Add indexes
            $table->index('email');
            $table->index('role');
            $table->index(['is_active', 'role']);
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove new fields
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['is_active', 'role']);
            $table->dropIndex(['role']);
            $table->dropIndex(['email']);

            $table->dropColumn(['preferences', 'last_login_at', 'is_active', 'role', 'phone', 'last_name', 'first_name']);

            // Drop UUID primary key
            $table->dropPrimary();
            $table->dropColumn('id');

            // Restore auto-increment primary key
            $table->id();
        });
    }
};
