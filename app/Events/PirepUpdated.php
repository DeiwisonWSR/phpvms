<?php

namespace App\Events;

use App\Models\Pirep;

class PirepUpdated extends BaseEvent
{
    public $pirep;

    public function __construct(Pirep $pirep)
    {
        $this->pirep = $pirep;
    }
}
