<?php

namespace Modules\Ticket\Entities;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Project\Entities\ProjectTask;
use Modules\Ticket\Http\Traits\TicketTrait;
use Modules\Project\Entities\Project;
use Modules\Ticket\Entities\TicketAttachment;

class Ticket extends Model
{
    use TicketTrait;
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'subject',
        'ticket_type_id',
        'ticket_source',
        'author_email',
        'operator_id',
        'author_full_name',
        'text',
        'priority',
        'customer_priority',
        'timing',
        'project_id',
        'status',
        'project_task_id',
        'status_label',
        'ticket_messages',
        'operation_note',
        'urge_date',
        'type_operation'
    ];
    protected $appends = ['status_label'];

        protected $dates = ['urge_date'];

    public function getStatusLabelAttribute()
    {

        return $this->getStatusNameLabel();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return \App\Helpers\EncodingHelper::fixUTF8($this->name);
    }
    public static function fieldsEditShow($ticket,$ticket_types){
        return self::fieldsEdit($ticket,$ticket_types);
    }

    public static function fieldsEditOperatorShow($ticket){
        return self::fieldsEditOperator($ticket);
    }
    public static function fieldsConvertToTaskShow($ticket){
        return self::fieldsConvertToTaskOperator($ticket);
    }
    public static function fieldsTicketMessageShow($ticket){
        return self::fieldsTicketMessage($ticket);
    }
    /**
     * @param $query
     * @param array $filters
     * @return mixed
     */
    public function scopeFilters($query, $filters = [])
    {
        if (!empty($filters['author_full_name'])) {
            $query->where('author_full_name','like',"%".$filters['author_full_name']."%");
        }
        if (!empty($filters['subject'])) {
            $query->where('subject','like',"%".$filters['subject']."%");
        }
        if (!empty($filters['priority'])) {
            $query->where('priority',$filters['priority']);
        }
        if (!empty($filters['ticket_type_id'])) {
            $query->where('ticket_type_id',$filters['ticket_type_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status',$filters['status']);
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id',$filters['project_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('created_at', ">=", \Carbon\Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', "<=", \Carbon\Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (!empty($filters['id'])) {
            $query->whereIn('id', is_array($filters['id']) ? $filters['id'] : array($filters['id']));
        }
        if (isset($filters['sort'])) {
            $orderby = explode(' ', $filters['sort']);
            if (isset($this->orderby_mapping[$orderby[0]])) {
                $query->orderBy($this->orderby_mapping[$orderby[0]], isset($orderby[1]) ? $orderby[1] : 'asc');
            } else {
                $query->orderBy($orderby[0], isset($orderby[1]) ? $orderby[1] : 'asc');
            }
        }
        return $query;
    }

    /**
     * @param array $filters
     * @param string $order_by
     * @return array
     */
    public static function toOptionList($filters = [], $order_by = 'id ASC')
    {
        $order_by = explode(' ', $order_by);

        $items = [];
        $l = Ticket::Filters($filters)->orderBy($order_by[0], $order_by[1])->get();
        foreach ($l as $i) {
            $items[$i->id] = $i->getName();
        }
        return $items;
    }

    /**
     * Send email notification to admin or customer
     */
    public function sendNotificationEmail()
    {

        // Subject
        $subject = 'Ticket ( ID: #' . $this->id . ') '. '| ' .$this->subject . ' | Stato: '. $this->getStatusName();

        if( Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('user') ) {
            // Send email to customer
            $messageText = view('ticket::emails.customer-ticket-notification',[ 'ticket' => $this ]);
        }
        if( Auth::user()->hasRole('customer') ) {
            // Send email to admin
            $messageText = view('ticket::emails.admin-ticket-notification',[ 'ticket' => $this ]);
        }

        if (!empty($messageText)) {
            \App\Helpers\MailHelper::sendEmail($messageText, $subject, $this->user );
        }
    }
    /**
     * Return the status Name
     * @return array|null|string
     */
    public function getStatusName()
    {
        //dd($this->status);
        return __('ticket::main.'.$this->status);
    }
    public function getStatusNameLabel(){
        $label = 'success';

        if ( $this->status == 'open' ) {
            return 'success';
        }
        if ( $this->status == 'request_submitted' ) {
            return 'primary';
        }
        if ( $this->status == 'in_process' ) {
            return 'warning';
        }
        if ($this->status == 'testing' ) {
            return 'dark';
        }
        if ($this->status == 'open' ) {
            return 'success';
        }
        if ($this->status == 'closed' ) {
            return 'danger';
        }
        if ( $this->status == 'waiting_reply' ) {
            return 'warning';
        }
        return $label;
    }

    /****************************************************
     *
     * RELATIONSHIPS
     *
     ***************************************************/


    public function ticketToken()
    {
        return $this->belongsTo('Modules\Ticket\Entities\TicketToken','ticket_token','token');
    }
    public function ticketType()
    {
        return $this->belongsTo(TicketType::class)->withDefault();
    }

    public function operator()
    {
        return $this->belongsTo('App\User','operator_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function projectTasks()
    {
        return $this->hasMany(ProjectTask::class);
    }
    public function ticketAttachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function priorityTranslation($value)
    {
        $priorities = [
            'high' => 'Alta',
            'medium' => 'Media',
            'low' => 'Bassa',
        ];
        return isset($priorities[$value]) ? $priorities[$value] : '-';
    }

    public function statusTranslation($value)
    {
        $statuses = [
            'request_submitted'=>'Richiesta inoltrata',
            'open'=>'Aperto',
            'in_process'=>'In lavorazione',
            'testing'=>'In testing',
            'closed'=>'Chiuso'
        ];
        return isset($statuses[$value]) ? $statuses[$value] : '-';
    }

    public function typeOperationTranslation($value)
    {
        $statuses = [
            'close_with_result'=>'Chiuso con esito',
            'close_without_result'=>'Chiuso senza esito'
        ];
        return isset($statuses[$value]) ? $statuses[$value] : '-';
    }
}
