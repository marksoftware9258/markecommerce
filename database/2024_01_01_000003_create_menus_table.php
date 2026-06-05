<?php

// ============================================================
// FILE: database/migrations/2024_01_01_000003_create_menus_table.php
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menus')
                ->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('url')->nullable();
            $table->string('route_name')->nullable();
            $table->string('icon')->nullable();
            $table->string('type')->default('sidebar'); // sidebar|topbar|footer
            $table->string('target')->default('_self');
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('guard_name')->default('web');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['type', 'is_active', 'order']);
            $table->index('parent_id');
        });

        // Menu <-> Role pivot
        Schema::create('menu_role', function (Blueprint $table) {
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['menu_id', 'role_id']);
        });

        // Menu <-> Permission pivot
        Schema::create('menu_permission', function (Blueprint $table) {
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['menu_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_permission');
        Schema::dropIfExists('menu_role');
        Schema::dropIfExists('menus');
    }
};
