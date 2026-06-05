<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // ── menus ─────────────────────────────────────────────────
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('url')->nullable();
            $table->string('route_name')->nullable();
            $table->string('icon')->nullable();
            $table->string('type')->default('sidebar');
            $table->string('target')->default('_self');
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('guard_name')->default('web');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['type', 'is_active', 'order']);
        });

        // Self-referencing FK added after table exists
        Schema::table('menus', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')->on('menus')
                  ->nullOnDelete();
        });

        // ── menu_role pivot ───────────────────────────────────────
        // Intentionally no FK to `roles` — avoids migration order
        // dependency on Spatie's table. Enforced at app layer.
        Schema::create('menu_role', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['menu_id', 'role_id']);
            $table->index('menu_id');
            $table->index('role_id');
        });

        // ── menu_permission pivot ─────────────────────────────────
        Schema::create('menu_permission', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('permission_id');
            $table->primary(['menu_id', 'permission_id']);
            $table->index('menu_id');
            $table->index('permission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_permission');
        Schema::dropIfExists('menu_role');
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        Schema::dropIfExists('menus');
    }
};
