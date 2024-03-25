<?php
namespace Modules\Ticket\Http\Traits;

use Illuminate\Http\Request;
use Modules\Ticket\Entities\TicketToken;
use Auth;
use Config;
use Form;
use Lang;

trait TicketTokenTrait
{
	/**
     * list of fields for ticket type
     * @return Arrray
     */
    private function ticketTokenFields($ticketToken=null){
        $f['name'] = array(
            'col'  => '12',
            'label'  => Form::label('name', __('main.name'), [ 'class' => 'required' ] ),
            'value' => Form::text('name', !empty($ticketToken)?$ticketToken->name:null, array('class' => 'form-control required','placeholder' => __('main.name'),'required' => true))
        );
        $f['project_id'] = array(
            'col' => '12',
            'label' => Form::label('project_id', 'Project'),
            'value' => Form::select(
                'project_id',
                \Modules\Project\Entities\Project::pluck('name', 'id'),
                [!empty($ticketToken)?$ticketToken->project_id:null],
                array(
                    'class' => 'form-control select2 required',
                    'id' => 'project_id',
                    'multiple' => false,
                )
            )
        );

        return $f;
    }

}