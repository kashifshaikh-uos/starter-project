<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('privileges', function (Blueprint $table) {
            $table->renameColumn('route', 'frontend_route');
        });

        Schema::table('privileges', function (Blueprint $table) {
            $table->string('api_route')->nullable()->after('frontend_route');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('privileges', function (Blueprint $table) {
            $table->dropColumn('api_route');
        });

        Schema::table('privileges', function (Blueprint $table) {
            $table->renameColumn('frontend_route', 'route');
        });
    }
};
