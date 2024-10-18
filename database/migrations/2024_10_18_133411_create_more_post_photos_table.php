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
        Schema::create('more_post_photos', function (Blueprint $table) {
            $table->id();
            $table->string('url', 255);
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
            $table->index('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('more_post_photos');
    }
};
