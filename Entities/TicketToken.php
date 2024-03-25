<?php

namespace Modules\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Ticket\Http\Traits\TicketTokenTrait;
use Modules\Project\Entities\Project;
class TicketToken extends Model
{
    use HasFactory;
    use TicketTokenTrait;

    protected $fillable = [
        'name',
        'token',
        'project_id'
    ];

    public function fields(){
        return $this->ticketTokenFields($this);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
