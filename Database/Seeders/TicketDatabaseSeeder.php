<?php

namespace Modules\Ticket\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TicketDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        /** Module products seeder **/
        $data = [
            'name' => 'Modulo Ticket',
            'slug' => 'mod_ticket',
            'price_month' => 50,
            'description' => 'Modulo Ticket',
            'active' => 1,
            'start_date' => '2018-04-04 21:35:01',
            'expire_date' => '2019-07-05 21:35:01',
        ];
        \App\Module::create($data);
        // $this->call(TicketTypeTableSeeder::class);
    }
}
