<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinalizeConvertUsersIdToUuid extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE sud_logs
            DROP CONSTRAINT IF EXISTS "sud_logs_user_id_foreign"
        ');
        DB::statement('
            ALTER TABLE sud_logs
            DROP CONSTRAINT IF EXISTS "sud_logs_user_uuid_foreign"
        ');

        DB::statement('
            ALTER TABLE users
            DROP CONSTRAINT IF EXISTS users_pkey
        ');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('uuid', 'id');
        });

        DB::statement('
            ALTER TABLE users
            ADD CONSTRAINT users_pkey PRIMARY KEY (id)
        ');

        Schema::table('sud_logs', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {

        DB::statement('
            ALTER TABLE sud_logs
            DROP CONSTRAINT IF EXISTS "sud_logs_user_id_foreign"
        ');

        DB::statement('
            ALTER TABLE users
            DROP CONSTRAINT IF EXISTS users_pkey
        ');

        Schema::table('users', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::statement('
            ALTER TABLE users
            ADD CONSTRAINT users_pkey PRIMARY KEY (id)
        ');

        Schema::table('sud_logs', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
}
