<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShHousesTable extends Migration
{
    /**
     * 贝壳二手房 房源表
     */
    public function up()
    {
        Schema::create('sh_houses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('city')->default('');
            $table->string('desc')->default('');
            $table->decimal('price',20,2)->default(0.00);
            $table->decimal('area',20,2)->default(0.00);
            $table->string('tags')->default('');
            $table->text('house_division');
            $table->string('area_name')->default('');
            $table->string('visit_time')->default('');
            $table->text('base_info');
            $table->text('feature');
            $table->text('pictures');
            $table->text('community');
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('sh_houses');
    }
}
