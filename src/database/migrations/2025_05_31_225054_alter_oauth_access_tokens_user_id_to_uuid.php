<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterOauthAccessTokensUserIdToUuid extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE oauth_access_tokens
            DROP CONSTRAINT IF EXISTS "oauth_access_tokens_user_id_foreign"
        ');
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->uuid('user_id')->after('id')->nullable();
        });
        DB::statement('
            UPDATE oauth_access_tokens
            SET user_id = \'00000000-0000-0000-0000-000000000000\'
            WHERE user_id IS NULL
        ');

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->uuid('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE oauth_access_tokens
            DROP CONSTRAINT IF EXISTS "oauth_access_tokens_user_id_foreign"
        ');
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
}
