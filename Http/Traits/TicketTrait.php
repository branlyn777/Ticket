<?php
namespace Modules\Ticket\Http\Traits;

use Illuminate\Http\Request;
use Modules\Project\Entities\ProjectTaskStatus;
use Modules\Ticket\Entities\Ticket;
use App\User;
use Auth;
use Config;
use Form;
use Lang;
use Modules\Ticket\Entities\TicketType;
use Modules\Project\Entities\Project;
use Illuminate\Testing\Constraints\SoftDeletedInDatabase;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\EncodingHelper;
use DOMDocument;

trait TicketTrait
{
//    use SoftDeletes;
    /**
     * @param Ticket|null $item
     * @return mixed
     */
    private function fields($item = null,$ticket_types = [])
    {

        $isScrivania = \App\Helpers\HostHelper::isScrivania();
        if ($isScrivania){
            if( Auth::user()->isAdmin() && $isScrivania) {
                $project=Project::get()->pluck('name', 'id');
            }else{
                $project= Auth::user()->projectPermissions()->with('project')->get()->pluck('project')->pluck('name','id');
            }
        }



        $f['subject'] = array(
            'col'  => '6',
            'label'  => Form::label('subject', Lang::get('main.subject'),['class'=>'']),
            'value' => Form::text('subject', null, array('class' => 'form-control ','placeholder' => Lang::get('main.subject')))
        );

        $f['ticket_type_id'] = array(
            'col'  => '6',
            'label'  => Form::label('ticket_type_id', Lang::get('main.type'),['class'=>'required']),
            'value' => Form::select(
                'ticket_type_id',
                $ticket_types,
                null,
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'ticket_type_id',
                    'placeholder' => 'Scegliere',
                    'required' => true
                )
            )
        );
        $f['priority'] = array(
            'col'  => '6',
            'label'  => Form::label('priority', Lang::get('ticket::main.priority'),['class'=>'required']),
            'value' => Form::select(
                'priority',
                ['high'=>'Alta','medium'=>'Media','low'=>'Bassa'],
                null,
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'priority',
                    'placeholder' => 'Scegliere',
                    'required' => true
                )
            )
        );
        if ($isScrivania) {
            $f['customer_priority'] = array(
                'col'  => '6',
                'label'  => Form::label('customer_priority', Lang::get('ticket::main.customer_priority'),['class'=>'required']),
                'value' => Form::select(
                    'customer_priority',
                    ['high'=>'Alta','medium'=>'Media','low'=>'Bassa'],
                    null,
                    array(
                        'class'       => 'form-control required',
                        'placeholder' => Lang::get('main.choice'),
                        'id' => 'customer_priority',
                        'placeholder' => 'Scegliere',
                        'required' => true
                    )
                )
            );
        }
        $f['text'] = array(
            'col'  => '12',
            'label'  => Form::label('text', Lang::get('main.message'), array('class' => 'required')),
            'value' => Form::textarea('text', null, array('class' => 'form-control required ticket-description tinymce-editor description_' ,'placeholder' => 'Messaggio'))
        );
        if ($isScrivania) {
            $f['project_id'] = array(
                'col' => '12',
                'label' => Form::label('project_id', 'Progetto', ["class" => "required"]),
                'value' => Form::select(
                    'project_id',
                    $project,
                    [],
                    array(
                        'class' => 'form-control required',
                        'placeholder' => Lang::get('main.choice'),
                        'id' => 'project_id',
                        'required' => true
                    )

                )
            );
        }

        $uniqueId = EncodingHelper::generateRandomNumber(5);

        $f['add_file_section'] = array(
            'value' => '<div class="col-md-12"><span class="text-right pointer pull-right">
                            <i class="ti-clip icon-size pull-right pointer"></i>
                            <span class="pull-right add_file_btn" data-file-section-id="add-files-section-'.$uniqueId.'">Aggiungi Allegati</span>
                        </span></div>'
        );
        $f['open_div_attachments'] = array(
            'value' => '<div id="add-files-section-'.$uniqueId.'" class="col-md-12 p-0" style="display: none;"><hr style="margin: 10px;"><div class="col-md-12"><h4 class="text-center">Carica '.__('ticket::main.attachment').'</h4></div>'
        );
        $f['open_div'] = array(
            'value' => '<div id="files-list-'.$uniqueId.'" class="col-md-12 p-0">'
        );
        $f['file'] = array(
            'col_custom'  => 'col-md-12 file-item',
            'label'  => Form::label('file',__('ticket::main.attachment') ) . "<span class='remove-file pull-right text-danger pointer' style='display: none;'><i class='fa fa-remove'></i> Elimina</span> <br>",
            'value' => '<div class="row"><div class="col-md-6 pr-0"><input type="text" name="description[]" class="form-control" placeholder="Descrizione"></div><div class="col-md-6 pl-0"><input type="file" name="attachment[]" class="form-control input-group-append" accept=".jpeg, .jpg, .png, .doc, .docx, .xls, .xlsx"></div></div>'
        ); 
        $f['close_div'] = array(
            'value' => '</div>'
        );
        $f['add_file'] = array(
            'value' => '<div class="col-md-12 mt-4 text-center"><span class="pointer btn btn-outline-info add-file-to-list" data-files-list="files-list-'.$uniqueId.'"><i class="fa fa-plus"></i> Aggiungi file</span></div>'
        );
        $f['close_div_attachments'] = array(
            'value' => '</div>'
        );

        return $f;
    }
    private static function fieldsEditOperator($item = null){
        $f['operator_id'] = array(
            'col'  => '12',
            'label'  => Form::label('operator_id', __('ticket::main.operator'),['class'=>'required']),
            'value' => Form::select(
                'operator_id',
                User::toOptionList([]),
                !empty($item)?$item['operator_id']:null,
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'operator_id',
                    'placeholder' => 'Scegliere',
                    'required' => true
                )
            )
        );

        return $f;
    }

    private static function fieldsConvertToTaskOperator($item = null){

        $checkScrivania = \App\Helpers\HostHelper::isScrivania();
        if ($checkScrivania){
            if( Auth::user()->isAdmin() && $checkScrivania) {
                $project=Project::get()->pluck('name', 'id');
            }else{
                $project= Auth::user()->projectPermissions()->with('project')->get()->pluck('project')->pluck('name','id');
            }
        }

        if ($checkScrivania){
            $f['project_id'] = array(
                'col' => '6',
                'label' => Form::label('project_id', 'Progetto'),
                'value' => Form::select(
                    'project_id',
                    $project,
                    isset($item->project_id) ? $item->project_id : null,
                    array(
                        'class' => 'form-control required',
                        'placeholder' => Lang::get('main.choice'),
                        'id' => 'project_id',
                        'required' => true
                    )

                )
            );
        }


        $f['project_status_id'] = array(
            'col' => '6',
            'label' => Form::label('slug', 'Stato Task'),
            'value' => Form::select(
                'project_status_id',
                ProjectTaskStatus::toOptionList(),
                [],
                array(
                    'class' => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'project_status_id',
                    'required' => true
                )
            )
        );

        //Assegnato a
        $f['operator_id'] = array(
            'col'  => '12',
            'label' => Form::label('operator_id', 'Assegnato a'),
            'value' => Form::select(
                'operator_id',
                User::toOptionList(),
                [],
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'operator_id',
                    'placeholder' => 'Scegliere',
                    'required' => true
                )
            )
        );

        $f['priority'] = array(
            'col' => '12',
            'label' => Form::label('priority', 'Priorita'),
            'value' => Form::select(
                'priority',
                ['high' => 'Alta', 'medium' => 'Media', 'low' => 'Bassa'],
                [],
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'priority',
                    'placeholder' => 'Scegliere',
                    'required' => true
                )
            ),
        );

        $f['date_start'] = array(
            'col'  => '6',
            'label'  => Form::label('date_start', 'Data di inizio'),
            'value' => Form::date('date_start', null, array('class' => 'form-control', 'placeholder' => 'Data di inizio'))
        );

        $f['expiry_date'] = array(
            'col'  => '6',
            'label'  => Form::label('expiry_date', 'Data di scadenza'),
            'value' => Form::date('expiry_date', null, array('class' => 'form-control', 'placeholder' => 'Data di scadenza'))
        );



        return $f;
    }

    static function fieldsEdit($item = null,$ticket_types = [])
    {
        $checkScrivania = \App\Helpers\HostHelper::isScrivania();

        $f['subject'] = array(
            'col'  => '6',
            'label'  => Form::label('subject', Lang::get('main.subject'),['class'=>'required']),
            'value' => Form::text('subject', !empty($item)?$item->subject:null, array('class' => 'form-control required ','placeholder' => Lang::get('main.subject'),'required' => true))
        );

        $f['ticket_type_id'] = array(
            'col'  => '6',
            'label'  => Form::label('ticket_type_id', Lang::get('main.type'),['class'=>'required']),
            'value' => Form::select(
                'ticket_type_id',
                $ticket_types,
                !empty($item)?$item->ticket_type_id:null,
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'ticket_type_id',
                    'placeholder' => 'Scegliere',
                    'required' => true
                )
            )
        );

        $ticket_statuses = ['request_submitted'=>'Richiesta inoltrata','open'=>'Aperto','in_process'=>'In lavorazione','testing'=>'In testing','waiting_integration' => 'In attesa integrazione','closed'=>'Chiuso'];
        $readonly = $checkScrivania ? false : true;

        $f['status'] = array(
            'col'  => '6',
            'label'  => Form::label('status', Lang::get('main.user_status'),['class'=>'required']),
            'value' => Form::select(
                'status',
                $ticket_statuses,
                !empty($item)?$item->status:null,
                array(
                    'class'       => 'form-control required ',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'status',
                    'placeholder' => 'Scegliere',
                    'required' => true,
                    "disabled" => $readonly

                )
            )
        );
        $f['priority'] = array(
            'col'  => '6',
            'label'  => Form::label('priority', Lang::get('ticket::main.priority'),['class'=>'required']),
            'value' => Form::select(
                'priority',
                ['high'=>'Alta','medium'=>'Media','low'=>'Bassa'],
                !empty($item)?$item->priority:null,
                array(
                    'class'       => 'form-control required ',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'priority',
                    'placeholder' => 'Scegliere',
                    'required' => true
                )
            )
        );
        if (\App\Helpers\HostHelper::isScrivania()) {
            $f['customer_priority'] = array(
                'col'  => '6',
                'label'  => Form::label('customer_priority', Lang::get('ticket::main.customer_priority'),['class'=>'required']),
                'value' => Form::select(
                    'customer_priority',
                    ['high'=>'Alta','medium'=>'Media','low'=>'Bassa'],
                    !empty($item)?$item->customer_priority:null,
                    array(
                        'class'       => 'form-control required ',
                        'placeholder' => Lang::get('main.choice'),
                        'id' => 'customer_priority',
                        'placeholder' => 'Scegliere',
                        'required' => true
                    )
                )
            );
        }
        $f['timing'] = array(
            'col'  => '6',
            'label'  => Form::label('timing', Lang::get('ticket::main.timing')),
            'value' => Form::text('timing', !empty($item)?$item->timing:null, array('class' => 'form-control ','placeholder' => Lang::get('ticket::main.timing')))
        );
        $f['text'] = array(
            'col'  => '12',
            'label'  => Form::label('text', Lang::get('main.message'), array('class' => 'required')),
            'value' => Form::textarea('text', !empty($item)?$item->text:null, array('class' => 'form-control ticket-description tinymce-editor description_'.@$item->id ,'placeholder' => 'Messaggio'))
        );

        $required = (isset($item->status) && $item->status == 'closed') ? true : false;
        $dNoneClass = $required? '' : 'd-none';
        // if($required){    
        $f['type_operation'] = array(
            'col'  => '12 type-operation '.$dNoneClass,
            'label'  => Form::label('type_operation', __('ticket::main.type_intervation'), array('class' => 'required')),
            'value' => Form::select(
                    'type_operation',
                    ['close_with_result' => 'Chiuso con esito', 'close_without_result' => 'Chiuso senza esito'],
                    @$data['type_operation'],
                    array(
                        'class' => 'form-control',
                        'placeholder' => Lang::get('main.choice'),
                        'id' => 'type_operation',
                        'required' => $required,
                    )
                )
        );
        // }

        $f['operation_note'] = array(
            'col'  => '12 operation-note',
            'label'  => Form::label('operation_note', __('ticket::main.note_intervation'), array('class' => $required)),
            'value' => Form::textarea('operation_note', null, array('class' => 'form-control tinymce-editor description_'.@$item->id ,'placeholder' => __('ticket::main.note_intervation')))
        );

        $uniqueId = EncodingHelper::generateRandomNumber(5);

        // $f['add_file_section'] = array(
        //     'value' => '<div class="col-md-12 mb-2"><span class="text-right pointer pull-right">
        //                     <i class="ti-clip icon-size pull-right pointer"></i>
        //                     <span class="pull-right add_file_btn" data-file-section-id="add-files-section-'.$uniqueId.'">Aggiungi Allegati</span>
        //                 </span></div>'
        // );
        // $f['open_div_attachments'] = array(
        //     'value' => '<div id="add-files-section-'.$uniqueId.'" class="col-md-12 p-0" style="display: none;"><hr style="margin: 10px;"><div class="col-md-12"><h4 class="text-center">Carica '.__('ticket::main.attachment').'</h4></div>'
        // );
        // $f['open_div'] = array(
        //     'value' => '<div id="files-list-'.$uniqueId.'" class="col-md-12 p-0">'
        // );
        // $f['file'] = array(
        //     'col_custom'  => 'col-md-12 file-item',
        //     'label'  => Form::label('file',__('ticket::main.attachment') ) . "<span class='remove-file pull-right text-danger pointer' style='display: none;'><i class='fa fa-remove'></i> Elimina</span> <br>",
        //     'value' => '<div class="row"><div class="col-md-6 pr-0"><input type="text" name="description[]" class="form-control" placeholder="Descrizione"></div><div class="col-md-6 pl-0"><input type="file" name="attachment[]" class="form-control input-group-append" accept=".jpeg, .jpg, .png, .doc, .docx, .xls, .xlsx"></div></div>'
        // ); 
        // $f['close_div'] = array(
        //     'value' => '</div>'
        // );
        // $f['add_file'] = array(
        //     'value' => '<div class="col-md-12 mt-4 text-center"><span class="pointer btn btn-outline-info add-file-to-list" data-files-list="files-list-'.$uniqueId.'"><i class="fa fa-plus"></i> Aggiungi file</span></div>'
        // );
        // $f['close_div_attachments'] = array(
        //     'value' => '</div>'
        // );

        // $f['ticket_attachment'] = array(
        //     'col'  => '12',
        //     'label' => '',
        //     'value' => view('ticket::partials.table-attachments-ticket', ['ticket' => $item ])->render()
        // );
        return $f;
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function filters(array $data,$ticket_types)
    {

        $f['author_full_name'] = array(
            'col'  => '12',
            'label'  => Form::label('author_full_name', Lang::get('main.author')),
            'value' => Form::text('author_full_name', @$data['author_full_name'], array('class' => 'form-control required','placeholder' => Lang::get('main.author')))
        );

        $isScrivania = \App\Helpers\HostHelper::isScrivania();
        if ($isScrivania){
            $projects=Project::get()->pluck('name', 'id');

            $f['project_id'] = array(
                'col' => '12',
                'label' => Form::label('project_id', 'Progetto', ["class" => ""]),
                'value' => Form::select(
                    'project_id',
                    $projects,
                    @$data['project_id'],
                    array(
                        'class' => 'form-control',
                        'placeholder' => Lang::get('main.choice'),
                        'id' => 'project_id',
                        'required' => true
                    )
                )
            );
        }

        $f['ticket_type_id'] = array(
            'col'  => '12',
            'label'  => Form::label('ticket_type_id', Lang::get('main.type'),['class'=>'']),
            'value' => Form::select(
                'ticket_type_id',
                $ticket_types,
                @$data['ticket_type_id'],
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'ticket_type_id',
                    'placeholder' => 'Scegliere'
                )
            )
        );
        $f['priority'] = array(
            'col'  => '12',
            'label'  => Form::label('priority', Lang::get('ticket::main.priority'),['class'=>'']),
            'value' => Form::select(
                'priority',
                ['high'=>'Alta','medium'=>'Media','low'=>'Bassa'],
                @$data['priority'],
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'priority',
                    'placeholder' => 'Scegliere',
                )
            )
        );
        $f['status'] = array(
            'col'  => '12',
            'label'  => Form::label('status', Lang::get('main.user_status'),['class'=>'']),
            'value' => Form::select(
                'status',
                ['request_submitted'=>'Richiesta inoltrata','open'=>'Aperto','in_process'=>'In lavorazione','testing'=>'In testing','closed'=>'Chiuso'],
                @$data['status'],
                array(
                    'class'       => 'form-control required',
                    'placeholder' => Lang::get('main.choice'),
                    'id' => 'status',
                    'placeholder' => 'Scegliere'
                )
            )
        );

        $f['date_from'] = array(
            'col'  => '6',
            'label'  => Form::label('date_from', Lang::get('main.from_date')),
            'value' => Form::date('date_from', @$data['date_from'], array('class' => 'form-control','placeholder' => Lang::get('main.from_date')))
        );
        $f['date_to'] = array(
            'col'  => '6',
            'label'  => Form::label('date_to', Lang::get('main.to_date')),
            'value' => Form::date('date_to', @$data['date_to'], array('class' => 'form-control required ','placeholder' => Lang::get('main.to_date')))
        );
        return $f;
    }

    public function getTickets($data){

        $curl = curl_init();
        $url = config('services.tickets.domain').'tickets';

        $params = http_build_query($data);
        $url = $url."?".$params;
	
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'token: '.config('services.tickets.token')
            ),
        ));

        $response = curl_exec($curl);

        $response = json_decode($response,true);
        return $response;
    }

    public function saveTicket($data){
        $curl = curl_init();
        $post_data = $data;
        $files = !empty($data['attachment'])?$data['attachment']:[];

        $post_data['author_full_name'] = Auth::user()->getFullName();
        $post_data['author_email'] = Auth::user()->email;
        $post_data['author_id'] = Auth::user()->id;
        $post_data['ticket_source'] = config('app.name');
        $post_data['ticket_token'] = config('services.tickets.token');
        $post_data['status'] = 'open';

        if (!empty($data['text'])) {
        $dom = new DOMDocument();
        $dom->loadHTML($data['text'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);

            // url extract
            $aTags = $dom->getElementsByTagName('a');
            foreach ($aTags as $aTag) {
                $text = $aTag->textContent;
                
                $aTag->setAttribute('href', $text);
            }
            $content = $dom->saveHTML();
            $post_data['text'] = $content;
        }

        unset($post_data['attachment']);
        $files_uploaded = [];

        foreach ($files as $index => $file) {
            $path_to_save =  'public/module-ticket/tickets/';
            $fileName = \App\Helpers\FileHelper::uploadFile($path_to_save, $file);
            $full_path = storage_path('app/public/module-ticket/tickets/'.$fileName);
            $files[] = $full_path;
            $post_data['attachment[' . $index . ']'] = curl_file_create(
                realpath($full_path),
                mime_content_type($full_path),
                basename($full_path)
            );
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('services.tickets.domain').'tickets',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'token: '.config('services.tickets.token')
            ),
        ));

        $response = curl_exec($curl);

        foreach ($files_uploaded as $key => $file) {
            @unlink($file);
        }

        $response = json_decode($response,true);
        curl_close($curl);
        return $response;
    }

    public function updateTicket($data,$id){
        $curl = curl_init();
        $post_data = $data;
        unset($post_data['_method']);
        $files = !empty($data['attachment'])?$data['attachment']:[];

        unset($post_data['attachment']);
        $files_uploaded = [];

        foreach ($files as $index => $file) {
            $path_to_save =  'public/module-ticket/tickets/';
            $fileName = \App\Helpers\FileHelper::uploadFile($path_to_save, $file);
            $full_path = storage_path('app/public/module-ticket/tickets/'.$fileName);
            $files[] = $full_path;
            $post_data['attachment[' . $index . ']'] = curl_file_create(
                realpath($full_path),
                mime_content_type($full_path),
                basename($full_path)
            );
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('services.tickets.domain').'tickets/'.$id.'/update',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'token: '.config('services.tickets.token'),
            ),
        ));

        $response = curl_exec($curl);

        foreach ($files_uploaded as $key => $file) {
            @unlink($file);
        }
        
        $response = json_decode($response,true);
        curl_close($curl);
        return $response;
    }

    public function saveTicketMessage($data){
        $curl = curl_init();
        $post_data = $data;
        $files = !empty($data['file'])?$data['file']:[];
        $post_data['author_full_name'] = Auth::user()->getFullName();
        $post_data['author_email'] = Auth::user()->email;

        // save images from editor
        $content = [];
        $tinyImages = [];
        if (!empty($data['message'])) {
        $dom = new DOMDocument();
        $dom->loadHTML($data['message'], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $imgTags = $dom->getElementsByTagName('img');

            foreach ($imgTags as $imgTag) {
                $src = $imgTag->getAttribute('src');
                $tinyImages[] = $src;
            }

            // url extract
            $aTags = $dom->getElementsByTagName('a');
            foreach ($aTags as $aTag) {
                $text = $aTag->textContent;
                
                $aTag->setAttribute('href', $text);
            }
            $content = $dom->saveHTML();
            $post_data['message'] = $content;
        }
        
        $counter = 0;

        $files_uploaded = [];
        foreach ($files as $index => $file) {
            if (!empty($file) && $file != 'undefined' && $file instanceof \Illuminate\Http\UploadedFile) {
                $path_to_save =  'public/module-ticket/tickets/';
                $fileName = \App\Helpers\FileHelper::uploadFile($path_to_save, $file);
                $full_path = storage_path('app/public/module-ticket/tickets/'.$fileName);
                $files_uploaded[] = $full_path;
                $post_data["file[$counter]"] = curl_file_create(
                    realpath($full_path),
                    mime_content_type($full_path),
                    basename($full_path)
                );
                $counter++;
            }
        }

        // save images from editor
        $tinyFiles = [];
        if ($tinyImages) {
            foreach ($tinyImages as $index => $tinyImage) {
                $oldFilename = pathinfo($tinyImage, PATHINFO_FILENAME) . '.' . pathinfo($tinyImage, PATHINFO_EXTENSION);
                $sourceFile = 'public/module-ticket/ticket-text/'.$oldFilename;
                $destPath = 'public/module-ticket/tickets/';

                $microtime = microtime();
                $microtime = str_replace(['.', ' '], '_', $microtime);
                $newfileName = 'message_file_' . $microtime . '.' . pathinfo($tinyImage, PATHINFO_EXTENSION);
                \Storage::move($sourceFile, $destPath . $newfileName);
                $full_path = storage_path('app/public/module-ticket/tickets/'.$newfileName);

                $tinyFiles[] = $full_path;
                $post_data["file[$counter]"] = curl_file_create(
                    realpath($full_path),
                    mime_content_type($full_path),
                    basename($full_path)
                );
               $counter++;
            }
        }

        foreach ($data['description'] as $key => $description) {
            $post_data["description[$key]"] = $description;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('services.tickets.domain').'ticket-messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'token: '.config('services.tickets.token'),
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        foreach ($files_uploaded as $key => $file) {
            @unlink($file);
        }
        $response = json_decode($response,true);
        return $response;
    }

    private function orderPriority ($tickets){
        $tickets = collect($tickets);
        $tickets = $tickets->sortBy(function ($ticket) {
            switch ($ticket->customer_priority) {
                case 'high':
                    return 1;
                case 'medium':
                    return 2;
                case 'low':
                    return 3;
                default:
                    return 4;
            }
        });

        return $tickets;
    }

    // static function fieldsTicketMessage($item = null,$ticket_types = [])
    // {
    //     // $f['message'] = array(
    //     //     'col'  => '12',
    //     //     'label'  => Form::label('message', 'Message', array('class' => 'required')),
    //     //     'value' => Form::textarea('message', null, array('class' => 'form-control','placeholder' => 'Message', 'required' => 'required'))
    //     // );

    //     $f['message'] = array(
    //         'col'  => '12',
    //         'label'  => Form::label('message','Message',['class'=>'task-label-color']),
    //         'value' => Form::textarea('message', null, array('class' => 'form-control tinymce-editor message-tiny-editor', 'placeholder' => 'Message ...','rows' => 3, 'name' => 'message'))
    //     );

    //     $f['file'] = array(
    //         'col' => '12',
    //         'value' => Form::file('file', @$item->file, array('required' => false))
    //     );
        
    //     return $f;
    // }

    static function fieldsTicketMessage( $item = null )
    {
        $uniqueId = EncodingHelper::generateRandomNumber(5);
        $checkScrivania = \App\Helpers\HostHelper::isScrivania();

        if ($checkScrivania) {
            $f['private'] = array(
                'value' => '<div class="col-md-12"><span class="text-right pointer pull-right">
                <i class="ti-unlock icon-size pull-right pointer text-success private" style="display: none" data-value="1" data-toggle="tooltip" title="Pubblico"></i>
                <i class="ti-lock icon-size pointer text-danger private" data-value="0" data-toggle="tooltip" title="Privato"></i><input type="hidden" name="private"></div>'
            );
        }

        // note + altro
        $f['message'] = array(
            'col'  => '12',
            'label'  => Form::label('message',__('ticket::main.notes_update') ),
            'value' => Form::textarea('message', null, array('class' => 'form-control tinymce-editor message-tiny-editor', 'placeholder' => __('ticket::main.notes_update'), 'rows' => '5' ))
        );

        $f['add_file_section'] = array(
            'value' => '<div class="col-md-12"><span class="text-right pointer pull-right">
                            <i class="ti-clip icon-size pull-right pointer"></i>
                            <span class="pull-right add_file_btn" data-file-section-id="add-files-section-'.$uniqueId.'">Aggiungi Allegati</span>
                        </span></div>'
        );
        $f['open_div_attachments'] = array(
            'value' => '<div id="add-files-section-'.$uniqueId.'" class="col-md-12 p-0" style="display: none;"><hr style="margin: 10px;"><div class="col-md-12"><h4 class="text-center">Carica files</h4></div>'
        );
        $f['open_div'] = array(
            'value' => '<div id="files-list-'.$uniqueId.'" class="col-md-12 p-0">'
        );
        $f['file'] = array(
            'col_custom'  => 'col-md-12 file-item',
            'label'  => Form::label('file',__('main.file') ) . "<span class='remove-file pull-right text-danger pointer' style='display: none;'><i class='fa fa-remove'></i> Elimina</span> <br>",
            'value' => '<div class="row"><div class="col-md-6 pr-0"><input type="text" name="description[]" class="form-control" placeholder="Descrizione"></div><div class="col-md-6 pl-0"><input type="file" name="file[]" class="form-control input-group-append" accept=".jpeg, .jpg, .png, .doc, .docx, .xls, .xlsx"></div></div>'
        ); 
        $f['close_div'] = array(
            'value' => '</div>'
        );
        $f['add_file'] = array(
            'value' => '<div class="col-md-12 mt-4 text-center"><span class="pointer btn btn-outline-info add-file-to-list" data-files-list="files-list-'.$uniqueId.'"><i class="fa fa-plus"></i> Aggiungi file</span></div>'
        );
        $f['close_div_attachments'] = array(
            'value' => '</div>'
        );

        return $f;
    }

    public function getTicketHistories($data){

        $curl = curl_init();
        $url = config('services.tickets.domain').'ticket/ticket-histories';

        $params = http_build_query($data);
        $url = $url."?".$params;
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'token: '.config('services.tickets.token')
            ),
        ));

        $response = curl_exec($curl);

        $response = json_decode($response,true);
        return $response;
    }

    public function destroyTicketMessageFile($data){
        $curl = curl_init();
        $post_data = $data;
        curl_setopt_array($curl, array(
            CURLOPT_URL => config('services.tickets.domain').'ticket-message-files/'.$data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'token: '.config('services.tickets.token')
            ),
        ));

        $response = curl_exec($curl);

        $response = json_decode($response,true);
        curl_close($curl);
        return $response;
    }

    public function destroyTicketHistory($data){
        $curl = curl_init();
        $post_data = $data;
        curl_setopt_array($curl, array(
            CURLOPT_URL => config('services.tickets.domain').'ticket/ticket-histories/'.$data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'token: '.config('services.tickets.token')
            ),
        ));

        $response = curl_exec($curl);

        $response = json_decode($response,true);
        curl_close($curl);
        return $response;
    }

    public function urgeTicket($data,$id){
        $curl = curl_init();
        $post_data = $data;
        unset($post_data['_method']);
        $post_data['urge_name'] = Auth::user()->getFullName();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('services.tickets.domain').'tickets/update-ticket/'.$id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'token: '.config('services.tickets.token'),
            ),
        ));

        $response = curl_exec($curl);
        
        $response = json_decode($response,true);
        curl_close($curl);
        return $response;
    }
}
