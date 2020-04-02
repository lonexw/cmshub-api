<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(0)->comment('状态 0 草稿 1 发布');
            $table->json('content')->comment('数据');
            $table->integer('custom_id')->comment('表ID');
            $table->integer('project_id')->comment('项目ID');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `items` comment'自定义数据'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
