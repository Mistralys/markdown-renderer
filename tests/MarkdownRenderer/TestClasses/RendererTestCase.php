<?php

declare(strict_types=1);

namespace MarkdownRenderer\TestClasses;

use AppUtils\FileHelper\FolderInfo;
use PHPUnit\Framework\TestCase;

class RendererTestCase extends TestCase
{
    public const TEST_FILE_BASIC = 'basic-test.md';

    protected function getAssetsFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/../assets')->create();
    }
}
