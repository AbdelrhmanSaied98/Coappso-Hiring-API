<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('job_description_id');
            $table->foreign('job_description_id')->references('id')->on('job_descriptions')->onDelete('cascade');

            $table->string('country');
            $table->string('city');

            $table->integer('experience_min');
            $table->integer('experience_max');

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
        Schema::dropIfExists('tournaments');
    }
}
