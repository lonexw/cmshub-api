<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('表名，api时当做单数使用');
            $table->string('zh_name')->comment('显示表名');
            $table->string('description')->nullable()->comment('描述');
            $table->integer('project_id')->comment('关联项目');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `customs` comment'用户自定义表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customs');
    }
}
