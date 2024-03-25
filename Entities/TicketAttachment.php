<?php

namespace Modules\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachment',
        'ticket_id',
        'author_id',
        'description'
    ];

    public function ticket()
    {
        return $this->belongsTo('Modules\Ticket\Entities\Ticket','ticket_id');
    }

    public function author()
    {
        return $this->belongsTo('App\User','author_id');
    }
}