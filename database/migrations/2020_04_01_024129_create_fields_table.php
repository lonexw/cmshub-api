<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('字段名');
            $table->string('zh_name')->comment('显示字段名');
            $table->string('description')->nullable()->comment('描述');
            $table->boolean('is_required')->default(false)->comment('是否必填 true 是 false 否');
            $table->boolean('is_unique')->default(false)->comment('是否唯一 true 是 false 否');
            $table->boolean('is_multiple')->default(false)->comment('是否多值 true 是 false 否');
            $table->boolean('is_hide')->default(false)->comment('是否隐藏 true 是 false 否');
            $table->string('type')->comment('字段类型 单行文本 sign_text 多行文本 multi_text 富文本 rich_text 资源 asset');
            $table->integer('custom_id')->comment('关联自定义表ID');
            $table->integer('project_id')->comment('关联项目');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `projects` comment'用户自定义表字段'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fields');
    }
}
