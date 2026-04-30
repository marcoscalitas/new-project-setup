<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['roles', 'permissions'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->char('ulid', 26)->nullable()->unique()->after('id');
            });

            \DB::table($table)->orderBy('id')->each(function ($row) use ($table) {
                \DB::table($table)
                    ->where('id', $row->id)
                    ->update(['ulid' => (string) Str::ulid()]);
            });

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->char('ulid', 26)->nullable(false)->change();
            });

            // SQLite DBAL rewrites the table on ->change(), stripping partial index WHERE clauses
            if (config('database.default') === 'sqlite') {
                $indexName = $table . '_name_guard_name_unique';
                \DB::statement("DROP INDEX IF EXISTS {$indexName}");
                \DB::statement("CREATE UNIQUE INDEX {$indexName} ON {$table} (name, guard_name) WHERE deleted_at IS NULL");
            }
        }
    }

    public function down(): void
    {
        foreach (['roles', 'permissions'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('ulid');
            });
        }
    }
};
