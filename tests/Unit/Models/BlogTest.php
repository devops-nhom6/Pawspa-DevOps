<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Blog;
use PHPUnit\Framework\TestCase;

class BlogTest extends TestCase
{
    public function testCreateSlugConvertsVietnameseAndSpecialCharacters(): void
    {
        $title = 'Tắm & Cắt tỉa cho chó mèo!!!';

        $slug = Blog::createSlug($title);

        $this->assertSame('tam-cat-tia-cho-cho-meo', $slug);
    }

    public function testCreateSlugCollapsesAndTrimsDashes(): void
    {
        $title = '---Xin   chào---';

        $slug = Blog::createSlug($title);

        $this->assertSame('xin-chao', $slug);
    }

    public function testGetExcerptReturnsExistingExcerptWhenPresent(): void
    {
        $blog = new Blog();
        $blog->excerpt = 'Tom tat ngan';
        $blog->content = '<p>Noi dung dai</p>';

        $this->assertSame('Tom tat ngan', $blog->getExcerpt());
    }

    public function testGetExcerptStripsHtmlTags(): void
    {
        $blog = new Blog();
        $blog->excerpt = '';
        $blog->content = '<p>Xin <strong>chao</strong> ban</p>';

        $this->assertSame('Xin chao ban', $blog->getExcerpt(100));
    }

    public function testGetExcerptTruncatesAndAddsEllipsis(): void
    {
        $blog = new Blog();
        $blog->excerpt = '';
        $blog->content = 'abcdefghijklmnopqrstuvwxyz';

        $this->assertSame('abcde...', $blog->getExcerpt(5));
    }

    public function testGetExcerptReturnsOriginalWhenShorterThanLimit(): void
    {
        $blog = new Blog();
        $blog->excerpt = '';
        $blog->content = 'ngan';

        $this->assertSame('ngan', $blog->getExcerpt(10));
    }
}
