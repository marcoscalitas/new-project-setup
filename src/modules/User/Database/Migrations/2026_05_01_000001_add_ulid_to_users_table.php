<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('ulid', 26)->nullable()->unique()->after('id');
        });

        \DB::table('users')->orderBy('id')->each(function ($user) {
            \DB::table('users')
                ->where('id', $user->id)
                ->update(['ulid' => (string) Str::ulid()]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->char('ulid', 26)->nullable(false)->change();
        });

        // SQLite DBAL rewrites the table on ->change(), stripping partial index WHERE clauses
        if (config('database.default') === 'sqlite') {
            \DB::statement('DROP INDEX IF EXISTS users_email_unique');
            \DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ulid');
        });
    }
};
