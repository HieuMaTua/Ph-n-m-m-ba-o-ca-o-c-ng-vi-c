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
        Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');           
        $table->text('description')->nullable(); 
        $table->enum('status', ['pending','in_progress','completed','overdue']); // Trạng thái
        $table->date('deadline')->nullable();
        $table->integer('progress')->default(0); // tiến độ %, 0 -     
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};