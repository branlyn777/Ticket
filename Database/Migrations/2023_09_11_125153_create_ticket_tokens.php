<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $checkScrivania = \App\Helpers\HostHelper::isScrivania();
        if ($checkScrivania){
            Schema::create('ticket_tokens', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('project_id')->unsigned()->nullable();
                $table->foreign('project_id')->references('id')->on('projects');
                $table->string('name');
                $table->string('token');
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
