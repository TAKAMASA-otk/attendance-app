<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreaksTable extends Migration
{
    public function up()
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();

            // 勤怠と紐付け
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');

            // 休憩開始・終了
            $table->timestamp('break_start');
            $table->timestamp('break_end')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('breaks');
    }
}
