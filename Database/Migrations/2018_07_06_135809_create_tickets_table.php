<?php

use App\Helpers\HostHelper;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $checkScrivania = HostHelper::isScrivania();
        if ($checkScrivania){
            Schema::create('tickets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('subject');
                $table->bigInteger('ticket_type_id')->unsigned();
                $table->foreign('ticket_type_id')
                      ->references('id')->on('ticket_types');
                $table->string('ticket_source');
                $table->string('author_email')->nullable();

                $table->bigInteger('operator_id')->unsigned()->nullable();
                $table->foreign('operator_id')
                      ->references('id')->on('users');

                $table->string('author_full_name');
                $table->longText('text');
                $table->string('status')->default('open');
                $table->string('priority');
                $table->string('customer_priority')->nullable();
                $table->decimal('timing')->nullable();

                $table->bigInteger('project_id')->unsigned()->nullable();
                $table->foreign('project_id')
                    ->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');

                $table->bigInteger('project_task_id')->unsigned()->nullable();
                $table->foreign('project_task_id')
                    ->references('id')->on('project_tasks')->onUpdate('cascade')->onDelete('cascade');

                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('tickets');
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
