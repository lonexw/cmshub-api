<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token')->comment('api访问token');
            $table->string('description')->nullable()->comment('描述');
            $table->json('custom_ids')->comment('设置权限的表名，数组，可以多个');
            $table->json('scopes')->comment('权限 query 查询/mutation 增删改/open 开放');
            $table->integer('project_id')->comment('项目ID');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `tokens` comment'api token表'");

        Schema::create('custom_token', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('token_id')->comment('token_id');
            $table->integer('custom_id')->nullable()->comment('custom_id');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `custom_token` comment'api token对应的自定义表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}
