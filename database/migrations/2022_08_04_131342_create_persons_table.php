<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class
CreatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('image')->nullable();
            $table->string('device_token');
            $table->string('password');
            $table->date('birth_date');
            $table->enum('gender' , ['Male','Female']);
            $table->string('country');
            $table->string('city');
            $table->string('nationality');
            $table->integer('min_salary');
            $table->boolean('isHideSalary');
            $table->string('military_status')->nullable();
            $table->string('marital_status')->nullable();
            $table->boolean('derive_licence')->nullable();
            $table->string('cv')->nullable();
            $table->integer('verification_code')->nullable();
            $table->tinyInteger('isBlocked')->default(0);
            $table->integer('ban_times')->default(0);

            $table->unsignedBigInteger('carer_level_id');
            $table->foreign('carer_level_id')->references('id')->on('carer_levels')->onDelete('cascade');

            $table->unsignedBigInteger('experience_year_id');
            $table->foreign('experience_year_id')->references('id')->on('experience_years')->onDelete('cascade');


            $table->unsignedBigInteger('job_search_status_id');
            $table->foreign('job_search_status_id')->references('id')->on('job_search_status')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persons');
    }
}
