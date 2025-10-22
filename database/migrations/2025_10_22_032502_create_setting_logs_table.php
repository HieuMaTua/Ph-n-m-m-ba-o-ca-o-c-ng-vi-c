<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingLogsTable extends Migration
{
    /**
     * Chạy migration để tạo bảng setting_logs.
     */
    public function up()
    {
        Schema::create('setting_logs', function (Blueprint $table) {
            $table->id(); // Khóa chính
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Người thực hiện thay đổi
            $table->string('key'); // Tên cấu hình (ví dụ: max_file_size)
            $table->string('old_value')->nullable(); // Giá trị cũ
            $table->string('new_value'); // Giá trị mới
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    /**
     * Xóa bảng setting_logs khi rollback.
     */
    public function down()
    {
        Schema::dropIfExists('setting_logs');
    }
}