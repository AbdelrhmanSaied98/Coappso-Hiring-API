<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentQuestionChoosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_question_chooses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tournament_question_id');
            $table->foreign('tournament_question_id')->references('id')->on('tournament_questions')->onDelete('cascade');

            $table->string('choose');

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
        Schema::dropIfExists('tournament_question_chooses');
    }
}
