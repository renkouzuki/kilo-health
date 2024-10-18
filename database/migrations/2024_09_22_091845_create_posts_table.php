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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('content_type')->default('html');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('upload_media_id')->nullable()->constrained()->onDelete('set null');
            $table->string('thumbnail', 500);
            $table->integer('read_time')->comment('Read time in minutes');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['title', 'published_at', 'views', 'likes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
