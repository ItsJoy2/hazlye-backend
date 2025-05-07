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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('productId')->unique();
            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('regular_price', 10, 2);
            $table->decimal('Purchase_price', 10, 2);
            $table->string('main_image');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};