<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FinalizeConvertSudLogsUserIdToUuid extends Migration
{
    public function up(): void
    {
        // 1) Делаем user_uuid NOT NULL
        Schema::table('sud_logs', function (Blueprint $table) {
            $table->uuid('user_uuid')->nullable(false)->change();
        });

        // 2) Удаляем старый FK (если есть).
        //    Здесь полезно добавлять DROP CONSTRAINT IF EXISTS,
        //    чтобы не вылететь из-за “не найдено”.
        \DB::statement('
            ALTER TABLE sud_logs
            DROP CONSTRAINT IF EXISTS "sud_logs_user_id_foreign"
        ');

        // 3) Создаём новый FK: user_uuid → users.uuid
        Schema::table('sud_logs', function (Blueprint $table) {
            $table->foreign('user_uuid')
                ->references('uuid')
                ->on('users')
                ->cascadeOnDelete();
        });

        // 4) Удаляем старое поле user_id (integer)
        Schema::table('sud_logs', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        // 5) Переименовываем user_uuid → user_id (теперь это UUID)
        Schema::table('sud_logs', function (Blueprint $table) {
            $table->renameColumn('user_uuid', 'user_id');
        });
    }

    public function down(): void
    {
        \DB::statement('
            ALTER TABLE sud_logs
            DROP CONSTRAINT IF EXISTS "sud_logs_user_uuid_foreign"
        ');

        Schema::table('sud_logs', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_uuid');
        });

        Schema::table('sud_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        \DB::statement('
            UPDATE sud_logs
            SET user_id = users.id
            FROM users
            WHERE sud_logs.user_uuid = users.uuid
        ');

        Schema::table('sud_logs', function (Blueprint $table) {
            $table->dropColumn('user_uuid');
        });

        Schema::table('sud_logs', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
}
