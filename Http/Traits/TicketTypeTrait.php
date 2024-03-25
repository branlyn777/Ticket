<?php
namespace Modules\Ticket\Http\Traits;

use Illuminate\Http\Request;
use Modules\Ticket\Entities\TicketType;
use Auth;
use Config;
use Form;
use Lang;

trait TicketTypeTrait
{
	/**
     * list of fields for ticket type
     * @return Arrray
     */
    public function ticketTypeFields(){

        $f['name'] = array(
            'col'  => '12',
            'label' => Form::label('name', __('ticket::main.type'), [ 'class' => 'required',"style" => "float:left" ] ),
            'value' => Form::text('name', null, array('class' => 'form-control required','placeholder' => __('ticket::main.type'),'required' => true))
        );
        return $f;
    }

}