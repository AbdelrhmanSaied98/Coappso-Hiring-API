<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('job_description_id');
            $table->foreign('job_description_id')->references('id')->on('job_descriptions')->onDelete('cascade');

            $table->unsignedBigInteger('job_type_id');
            $table->foreign('job_type_id')->references('id')->on('job_types')->onDelete('cascade');


            $table->unsignedBigInteger('education_level_id');
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('cascade');

            $table->string('country');
            $table->string('city');

            $table->unsignedBigInteger('carer_level_id');
            $table->foreign('carer_level_id')->references('id')->on('carer_levels')->onDelete('cascade');

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->integer('experience_min');
            $table->integer('experience_max');
            $table->integer('salary_min');
            $table->integer('salary_max');
            $table->boolean('isHideSalary');
            $table->string('additionSalaryDetails');
            $table->integer('number_of_vacancies');

            $table->string('job_details');

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
        Schema::dropIfExists('jobs');
    }
}
