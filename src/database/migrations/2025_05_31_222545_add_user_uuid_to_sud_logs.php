<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUserUuidToSudLogs extends Migration
{
    public function up(): void
    {
        Schema::table('sud_logs', function (Blueprint $table) {
            $table->uuid('user_uuid')->nullable()->after('user_id');
        });

        DB::statement('
            UPDATE sud_logs
            SET user_uuid = users.uuid
            FROM users
            WHERE sud_logs.user_id = users.id
        ');
    }

    public function down(): void
    {
        Schema::table('sud_logs', function (Blueprint $table) {
            $table->dropColumn('user_uuid');
        });
    }
}
