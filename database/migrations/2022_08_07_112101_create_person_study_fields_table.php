<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonStudyFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_study_fields', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('person_id');
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');

            $table->unsignedBigInteger('education_level_id');
            $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('cascade');

            $table->unsignedBigInteger('job_study_field_id')->nullable();
            $table->foreign('job_study_field_id')->references('id')->on('job_study_fields')->onDelete('cascade');

            $table->string('institution_name');
            $table->string('certification_name')->nullable();
            $table->string('language_of_study')->nullable();
            $table->year('graduation_year');

            $table->unsignedBigInteger('grade_id');
            $table->foreign('grade_id')->references('id')->on('grades')->onDelete('cascade');

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
        Schema::dropIfExists('person_study_fields');
    }
}
