<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use PHPUnit\Framework\TestCase;

class LibHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['HTTP_HOST'] = 'localhost:8000';
        $_SERVER['SCRIPT_NAME'] = '/public/index.php';
    }

    public function testBaseUrlBuildsFromServerAndTrimsLeadingSlash(): void
    {
        $this->assertSame('http://localhost:8000/public/dashboard', base_url('/dashboard'));
    }

    public function testBaseUrlWithoutPathReturnsBaseOnly(): void
    {
        $this->assertSame('http://localhost:8000/public', base_url());
    }

    public function testActiveLinkExactMatchesOnlySamePath(): void
    {
        $_SERVER['REQUEST_URI'] = '/admin/users';

        $this->assertSame('active', active_link('/admin/users', 'active', true));
        $this->assertSame('', active_link('/admin', 'active', true));
    }

    public function testActiveLinkNonExactMatchesPrefix(): void
    {
        $_SERVER['REQUEST_URI'] = '/admin/users/list';

        $this->assertSame('active', active_link('/admin'));
    }

    public function testActiveLinkIgnoresQueryString(): void
    {
        $_SERVER['REQUEST_URI'] = '/bookings?page=2';

        $this->assertSame('active', active_link('/bookings', 'active', true));
    }
}
