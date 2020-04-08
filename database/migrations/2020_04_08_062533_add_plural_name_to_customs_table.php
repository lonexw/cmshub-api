<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPluralNameToCustomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customs', function (Blueprint $table) {
            $table->string('plural_name')->nullable()->comment('api时当做复数使用');
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
            $table->dropColumn('plural_name');
        });
    }
}
