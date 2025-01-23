<?php

namespace App\Http\Responses;

use Laravel\Fortify\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;

class FailedPasswordResetLinkRequestResponse extends SuccessfulPasswordResetLinkRequestResponse
{
    public function __construct(string $status)
    {
        parent::__construct($status);

        $this->status = 'passwords.sent';
    }
}
