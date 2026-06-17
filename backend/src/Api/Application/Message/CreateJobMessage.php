<?php

declare(strict_types=1);

namespace App\Api\Application\Message;

use Symfony\Component\Uid\Uuid;

class CreateJobMessage
{
    public Uuid $id;

    public function __construct(Uuid $id)
    {
        $this->id = $id;
    }
}
