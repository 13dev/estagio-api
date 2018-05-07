<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->unsignedInteger('destiny_id');
            $table->unsignedInteger('user_id');
            $table->string('desc', 1000);
            $table->enum('rating', [
                0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5
            ]);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('destiny_id')->references('id')->on('destiny');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
