<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['roles', 'permissions'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->ulid()->unique()->after('id');
            });
        }

        Model::clearBootedModels();
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
