<?php

namespace Tests\Feature\SendPortal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_requires_authentication(): void
    {
        $this->get('/sendportal/reports')->assertRedirect();
    }

    public function test_reports_page_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/sendportal/reports')
            ->assertStatus(200);
    }
}