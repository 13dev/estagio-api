<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 20)->index();
            $table->string('last_name', 20)->index();
            $table->string('email')->unique()->index();
            $table->string('password');
            $table->enum('role', [
                'user', 
                'admin', 
                'superadmin'
            ])->default('user');
            
            $table->tinyInteger('active')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
