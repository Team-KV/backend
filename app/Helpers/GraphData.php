<?php

namespace App\Helpers;

use DateTime;

class GraphData
{
    public string $dateTime;
    public int $progress;

    public function __construct(string $dateTime, int $progress)
    {
        $this->dateTime = $dateTime;
        $this->progress = $progress;
    }
}
