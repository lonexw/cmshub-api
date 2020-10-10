<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->comment('代码');
            $table->string('name')->nullable()->comment('名字');
            $table->integer('is_default')->default(0)->comment('是否默认 1默认');
            $table->integer('is_enabled')->default(1)->comment('是否可用 1可用');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `languages` comment'语言表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
