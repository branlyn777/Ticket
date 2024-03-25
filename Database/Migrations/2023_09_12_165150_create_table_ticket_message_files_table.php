<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTicketMessageFilesTable extends Migration
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
            Schema::create('ticket_message_files', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('ticket_message_id')->unsigned()->nullable();
                $table->foreign('ticket_message_id')
                    ->references('id')->on('ticket_messages');
                $table->string('file_path');
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
        Schema::table('', function (Blueprint $table) {

        });
    }
}
