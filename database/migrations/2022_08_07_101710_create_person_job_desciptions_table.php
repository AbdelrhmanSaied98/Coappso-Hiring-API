<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonJobDesciptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_job_desciptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('person_id');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');

            $table->unsignedBigInteger('job_description_id');
            $table->foreign('job_description_id')->references('id')->on('job_descriptions')->onDelete('cascade');

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
        Schema::dropIfExists('person_job_desciptions');
    }
}
