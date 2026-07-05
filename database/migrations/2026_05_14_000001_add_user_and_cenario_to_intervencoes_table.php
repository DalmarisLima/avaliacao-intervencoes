<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('intervencoes', function (Blueprint $table) {
            if (!Schema::hasColumn('intervencoes', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (!Schema::hasColumn('intervencoes', 'cenario')) {
                $table->string('cenario')->nullable()->after('turma');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intervencoes', function (Blueprint $table) {
            if (Schema::hasColumn('intervencoes', 'cenario')) {
                $table->dropColumn('cenario');
            }

            if (Schema::hasColumn('intervencoes', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
