<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert oauth tables' user_id columns to string to support UUID users.
     */
    public function up(): void
    {
        // Postgres requires USING to cast column types
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE varchar USING user_id::text");
            // oauth_auth_codes may also reference user_id
            if (Schema::hasTable('oauth_auth_codes') && Schema::hasColumn('oauth_auth_codes', 'user_id')) {
                DB::statement("ALTER TABLE oauth_auth_codes ALTER COLUMN user_id TYPE varchar USING user_id::text");
            }
        } else {
            // For MySQL and others, change column to string
            Schema::table('oauth_access_tokens', function (Blueprint $table) {
                if (Schema::hasColumn('oauth_access_tokens', 'user_id')) {
                    $table->string('user_id')->change();
                }
            });

            Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
                if (Schema::hasColumn('oauth_refresh_tokens', 'user_id')) {
                    $table->string('user_id')->change();
                }
            });

            if (Schema::hasTable('oauth_auth_codes') && Schema::hasColumn('oauth_auth_codes', 'user_id')) {
                Schema::table('oauth_auth_codes', function (Blueprint $table) {
                    $table->string('user_id')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     * WARNING: reverting may fail if UUID values are non-numeric.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Attempt to revert to bigint — this may fail if non-numeric values exist.
            DB::statement("ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE bigint USING user_id::bigint");
            if (Schema::hasTable('oauth_auth_codes') && Schema::hasColumn('oauth_auth_codes', 'user_id')) {
                DB::statement("ALTER TABLE oauth_auth_codes ALTER COLUMN user_id TYPE bigint USING user_id::bigint");
            }
        } else {
            Schema::table('oauth_access_tokens', function (Blueprint $table) {
                if (Schema::hasColumn('oauth_access_tokens', 'user_id')) {
                    $table->bigInteger('user_id')->change();
                }
            });

            Schema::table('oauth_refresh_tokens', function (Blueprint $table) {
                if (Schema::hasColumn('oauth_refresh_tokens', 'user_id')) {
                    $table->bigInteger('user_id')->change();
                }
            });

            if (Schema::hasTable('oauth_auth_codes') && Schema::hasColumn('oauth_auth_codes', 'user_id')) {
                Schema::table('oauth_auth_codes', function (Blueprint $table) {
                    $table->bigInteger('user_id')->change();
                });
            }
        }
    }
};
