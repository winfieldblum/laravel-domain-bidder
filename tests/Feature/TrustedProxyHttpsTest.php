<?php

test('marks the request as secure when X-Forwarded-Proto is https', function () {
    $this->call(
        'GET',
        '/up',
        server: [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.10',
        ],
    )->assertOk();

    expect(request()->secure())->toBeTrue();
});
