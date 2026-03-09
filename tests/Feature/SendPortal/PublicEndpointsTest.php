<?php

namespace Tests\Feature\SendPortal;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_unsubscribe_invalid_token_shows_page(): void
    {
        $this->get('/sp/public/unsubscribe/invalid-token')
            ->assertStatus(200);
    }
}