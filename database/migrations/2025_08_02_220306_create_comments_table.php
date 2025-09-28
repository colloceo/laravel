<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
{
    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        // Foreign key to link to the articles table
        $table->foreignId('article_id')->constrained()->onDelete('cascade');
        $table->string('author_name');
        $table->text('content');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
