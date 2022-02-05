<?php

declare(strict_types=1);

namespace DonnySim\Validation\Enums;

enum DataProcessStateEnum: int
{
    case IDLE = 1;
    case RUNNING = 2;
    case STOPPED = 3;
    case FINISHED = 4;
}
