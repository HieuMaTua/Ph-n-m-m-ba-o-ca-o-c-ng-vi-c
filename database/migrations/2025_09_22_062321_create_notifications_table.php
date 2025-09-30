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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('message'); // Nội dung thông báo
            $table->string('type')->default('info'); // Loại thông báo (info, success, warning, error)
            $table->unsignedBigInteger('user_id')->nullable(); // Liên kết với người dùng (nếu cần)
            $table->boolean('read')->default(false); // Trạng thái đã đọc
            $table->timestamps(); // created_at và updated_at
            
            // Khóa ngoại (nếu cần liên kết với bảng users)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};