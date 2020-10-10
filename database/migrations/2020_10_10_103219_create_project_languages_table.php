<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->default(0)->comment('项目id');
            $table->integer('language_id')->default(0)->comment('语言id');
            $table->string('code')->nullable()->comment('代码');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `project_languages` comment'项目语言表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_languages');
    }
}
