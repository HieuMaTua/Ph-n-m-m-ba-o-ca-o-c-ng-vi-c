<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Chạy migration để tạo bảng settings.
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); // Khóa chính
            $table->string('key')->unique(); // Tên cấu hình (ví dụ: max_file_size)
            $table->string('value'); // Giá trị cấu hình (ví dụ: 5)
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    /**
     * Xóa bảng settings khi rollback.
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}