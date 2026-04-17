<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('privilege_group_id')->nullable()->constrained('privilege_groups')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('privileges')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('route')->nullable();
            $table->string('method', 10)->nullable();
            $table->enum('menu_type', ['menu', 'submenu', 'none'])->default('none');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('show_in_menu')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privileges');
    }
};
