<?php
namespace ParagonIE\Gossamer\Tests\Release\Bundler;

use ParagonIE\Gossamer\Release\Bundler\GitDiffBundler;
use PHPUnit\Framework\TestCase;

/**
 * @covers GitDiffBundler
 */
class GitDiffBundlerTest extends TestCase
{
    /**
     * @before
     */
    public function before()
    {
        if (!is_callable('shell_exec')) {
            $this->markTestSkipped("shell_exec() is not callable");
        }
    }

    /**
     * This tests the git diff command on libgossamer's own git history.
     *
     * @throws \Exception
     * @psalm-suppress ReservedWord
     */
    public function testGitDiffTags()
    {
        $bundler = (new GitDiffBundler())
            ->setWorkDirectory(dirname(dirname(dirname(__DIR__))))
            ->setPreviousIdentifier('v0.1.0');

        // Non-empty
        $hash = hash('sha256', $bundler->getGitDiff('v0.2.0'));
        $this->assertSame('8e61594f221c4608a8e5e44376cb5dac08758b7c4224007a33fab993d5ba7470', $hash);

        // Empty
        $bundler->setPreviousIdentifier('v0.2.0');
        $this->assertSame('', $bundler->getGitDiff('v0.2.0'));

        $hash = hash('sha256', $bundler->getGitDiff('v0.2.1'));
        $this->assertSame('ba08d71a6b83ae6736722dc5e481ba6671ffd10e1f2576cd6c992c40095cbe25', $hash);
    }

    /**
     * This tests the git diff command on libgossamer's own git history.
     *
     * @throws \Exception
     * @psalm-suppress ReservedWord
     */
    public function testGitDiffCommits()
    {
        $bundler = (new GitDiffBundler())
            ->setWorkDirectory(dirname(dirname(dirname(__DIR__))))
            ->setPreviousIdentifier('062e0f46629dfd293aa5471890b8bda80b53b63b');

        // Non-empty
        $hash = hash('sha256', $bundler->getGitDiff('a6f424fe62edeec0ad56dd05b6f55307e930d9b0'));
        $this->assertSame('0262e46f0bb9205d58feed71da38ef33f2ed22617f9f8bd99b414dc09a6f3f05', $hash);

        // Empty
        $this->assertSame('', $bundler->getGitDiff('v0.4.0'));

        $bundler->setPreviousIdentifier('ddd1c02bdcabc893d2d66954fbd6483cb5a83128');
        $hash = hash('sha256', $bundler->getGitDiff('062e0f46629dfd293aa5471890b8bda80b53b63b'));
        $this->assertSame('505bd38e9194b2589918f21fa4346711d40a01ad82c196a2c4a1bafcf267f753', $hash);
    }
}
