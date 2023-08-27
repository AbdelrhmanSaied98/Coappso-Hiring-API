<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentCompetitorAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_competitor_answers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tournament_competitor_id');
            $table->foreign('tournament_competitor_id')->references('id')->on('tournament_competitors')->onDelete('cascade');

            $table->unsignedBigInteger('tournament_question_id');
            $table->foreign('tournament_question_id')->references('id')->on('tournament_questions')->onDelete('cascade');

            $table->string('answer');


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
        Schema::dropIfExists('tournament_competitor_answers');
    }
}
