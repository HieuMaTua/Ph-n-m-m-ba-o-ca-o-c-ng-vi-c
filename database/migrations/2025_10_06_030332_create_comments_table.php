<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade'); // Xóa comment khi xóa task
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Xóa comment khi xóa user
            $table->text('content'); // Nội dung bình luận (mô tả tiến độ)
            $table->string('file_path')->nullable(); // Đường dẫn file chứng minh
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};