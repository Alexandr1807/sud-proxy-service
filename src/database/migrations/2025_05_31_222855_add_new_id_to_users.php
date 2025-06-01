<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddNewIdToUsers extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('new_id')->nullable()->after('uuid');
        });

        DB::statement('
            UPDATE users
            SET new_id = users.uuid
            WHERE new_id IS NULL
        ');

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('new_id')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('new_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['new_id']);
            $table->dropColumn('new_id');
        });
    }
}
