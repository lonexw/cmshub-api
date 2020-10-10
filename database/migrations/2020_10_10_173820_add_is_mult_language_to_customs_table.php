<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsMultLanguageToCustomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customs', function (Blueprint $table) {
            $table->boolean('is_mult_language')->default(false)->comment('是否多语言 true 是 false 否');
        });
        Schema::table('fields', function (Blueprint $table) {
            $table->boolean('is_mult_language')->default(false)->comment('是否多语言 true 是 false 否');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customs', function (Blueprint $table) {
            $table->dropColumn('is_mult_language');
        });
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn('is_mult_language');
        });
    }
}
