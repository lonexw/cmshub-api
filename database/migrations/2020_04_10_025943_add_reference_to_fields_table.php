<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceToFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->boolean('is_main')->default(false)->comment('模型关联是否主表字段');
            $table->integer('reference_custom_id')->default(0)->comment('模型关联表ID');
            $table->integer('reference_field_id')->default(0)->comment('模型关联字段ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn('reference_custom_id');
            $table->dropColumn('reference_field_id');
            $table->dropColumn('is_main');
        });
    }
}
