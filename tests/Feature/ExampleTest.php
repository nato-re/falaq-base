<?php

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

it('returns a successful response', function () {
    $this->withoutVite();

    $response = $this->get('/');

    $response->assertStatus(200);
});
