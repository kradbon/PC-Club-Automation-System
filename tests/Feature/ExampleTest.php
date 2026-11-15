<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/en');
        $this->get('/en')->assertOk()->assertSee('GB CLUB');
    }

    public function test_public_pages_and_auth_screens_render(): void
    {
        foreach (['/en/club', '/en/events', '/en/setup', '/en/rooms', '/en/tournaments', '/en/join', '/en/login', '/en/forgot-password'] as $uri) {
            $this->get($uri)->assertOk();
        }
        $this->get('/en/prices')->assertRedirect('/en/rooms');
    }
}
