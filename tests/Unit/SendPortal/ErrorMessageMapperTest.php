<?php

namespace Tests\Unit\SendPortal;

use App\Support\SendPortal\ErrorMessageMapper;
use Tests\TestCase;

class ErrorMessageMapperTest extends TestCase
{
    public function test_it_maps_authentication_errors(): void
    {
        $mapper = new ErrorMessageMapper();

        $this->assertStringContainsString(
            'SMTP authentication failed',
            $mapper->map('535 5.7.8 Username and Password not accepted')
        );
    }
}