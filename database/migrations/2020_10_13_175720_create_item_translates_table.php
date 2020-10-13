<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemTranslatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_translates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->default(0)->comment('状态 0 草稿 1 发布');
            $table->json('content')->comment('数据');
            $table->integer('custom_id')->comment('表ID');
            $table->integer('project_id')->comment('项目ID');
            $table->integer('language_id')->default(0)->comment('语言');
            $table->string('code')->nullable()->comment('code');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `item_translates` comment'自定义多语言数据'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_translates');
    }
}
