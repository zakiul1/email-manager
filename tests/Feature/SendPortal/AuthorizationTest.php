<?php

namespace Tests\Feature\SendPortal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sendportal_reports_are_protected(): void
    {
        $this->get('/sendportal/reports')->assertRedirect();
    }

    public function test_authenticated_user_can_access_reports_with_current_default_policy(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/sendportal/reports')
            ->assertOk();
    }
}