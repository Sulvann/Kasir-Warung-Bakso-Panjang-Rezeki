<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->increments('ingredient_id');
            $table->string('name', 50);
            $table->decimal('stock', 12, 2)->default(0.00);
            $table->enum('unit', ['Gram', 'Kg', 'Pcs', 'Kantong']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deleted_at')->nullable()->default(null);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
