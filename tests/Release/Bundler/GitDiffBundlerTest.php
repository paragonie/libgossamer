<?php
namespace ParagonIE\Gossamer\Tests\Release\Bundler;

use ParagonIE\Gossamer\Release\Bundler\GitDiffBundler;
use PHPUnit\Framework\TestCase;

/**
 * @covers GitDiffBundler
 */
class GitDiffBundlerTest extends TestCase
{
    /** @var string $dir */
    protected $dir = '';

    /**
     * @beforeClass
     */
    public function before()
    {
        if (!empty($this->dir)) {
            return;
        }
        if (!is_callable('shell_exec')) {
            $this->markTestSkipped("shell_exec() is not callable");
        }
        $dir = __DIR__;
        do {
            $prev = $dir;
            $dir = realpath(dirname($dir));
        } while (!empty($dir) && $dir !== $prev && !is_dir($dir . '/.git'));
        if (empty($dir)) {
            $this->markTestSkipped("Cannot test in CI");
        }
        // Github Actions hack.
        if ($dir === '/home/runner/work/libgossamer/libgossamer') {
            $dummy = 'dummy-' . bin2hex(random_bytes(16));
            $dir = __DIR__ . '/' . $dummy;
            exec("git clone https://github.com/paragonie/libgossamer $dir");
        }
        $this->dir = $dir;
    }

    /**
     * This tests the git diff command on libgossamer's own git history.
     *
     * @throws \Exception
     * @psalm-suppress ReservedWord
     */
    public function testGitDiffTags()
    {
        if (empty($this->dir)) {
            $this->markTestSkipped("Cannot test in CI");
        }
        $this->assertNotEmpty($this->dir);
        $bundler = (new GitDiffBundler())
            ->setWorkDirectory($this->dir)
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
        if (empty($this->dir)) {
            $this->markTestSkipped("Cannot test in CI");
        }
        $this->assertNotEmpty($this->dir);
        $bundler = (new GitDiffBundler())
            ->setWorkDirectory($this->dir)
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
