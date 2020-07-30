<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('project_id')->default(0)->comment('项目ID');
            $table->string('title')->default('默认')->comment('分类名称');
            $table->timestamps();
        });
        \DB::statement("ALTER TABLE `categories` comment'表结构的分类'");
        Schema::table('customs', function (Blueprint $table) {
            $table->integer('category_id')->default(0)->comment('分类ID');
        });
        // 增加默认表机构分类
        $projects = \App\Models\Project::query()
            ->with('customs')
            ->get();
        foreach ($projects as $project) {
            // 添加项目表结构分类
            $category = new \App\Models\Category();
            $category->project_id = $project->id;
            $category->title = '默认';
            $category->save();
            // 修改表机构所属分类
            $customs = $project->customs;
            foreach ($customs as $custom) {
                $custom->category_id = $category->id;
                $custom->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
        Schema::table('customs', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
}
