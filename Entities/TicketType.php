<?php

namespace Modules\Ticket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Ticket\Http\Traits\TicketTypeTrait;

class TicketType extends Model
{
    use SoftDeletes;
    use TicketTypeTrait;
    protected $fillable = [
        'name'
       ];


    /**
     * @return mixed
     */
    public function getName()
    {
        return \App\Helpers\EncodingHelper::fixUTF8($this->name);
    }


    /**
     * @param $query
     * @param array $filters
     * @return mixed
     */
    public function scopeFilters($query, $filters = [])
    {
        if (isset($filters['name'])) {
            $query->where('name',$filters['name']);
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
        $l = TicketType::Filters($filters)
                                ->orderBy($order_by[0], $order_by[1])->get();
        foreach ($l as $i) {
            $items[$i->id] = $i->getName();
        }
        return $items;
    }

    public function fields($fieldsToShow=[])
    {
        return $this->ticketTypeFields($this);
    }


    /****************************************************
     *
     * RELATIONSHIPS
     *
     ***************************************************/

    public function ticket()
    {
        return $this->hasMany(Ticket::class,"ticket_type_id");
    }
}
