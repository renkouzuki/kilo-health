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
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->renameColumn('category_id', 'categorie_id');
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->renameColumn('categorie_id', 'category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }
};
