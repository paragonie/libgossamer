<?php
namespace ParagonIE\Gossamer\Release\Bundler;

use Exception;
use ParagonIE\Gossamer\GossamerException;
use ParagonIE\Gossamer\Interfaces\ReleaseBundlerInterface;
use ParagonIE\Gossamer\TypeHelperTrait;

class GitDiffBundler implements ReleaseBundlerInterface
{
    use TypeHelperTrait;

    const IDENTIFIER_REGEX = '#[a-zA-Z0-9\-_\.]+#';

    /** @var string $previousIdentifier */
    protected $previousIdentifier = '';

    /** @var string $directory */
    protected $directory = '';

    /**
     * @param string $directory
     * @return self
     * @throws Exception
     */
    public function setWorkDirectory($directory)
    {
        $this->assert(is_string($directory), "Argument 1 must be a string");
        $this->assert(is_dir($directory), "Given path is not a directory: {$directory}");
        $this->directory = $directory;
        return $this;
    }

    /**
     * @param string $commitOrTag
     * @return self
     * @throws Exception
     */
    public function setPreviousIdentifier($commitOrTag)
    {
        $this->assert(is_string($commitOrTag), "Argument 1 must be a string");
        $this->assert(
            preg_match(self::IDENTIFIER_REGEX, $commitOrTag) > 0,
            "Invalid commit or tag identifier"
        );
        $this->previousIdentifier = $commitOrTag;
        return $this;
    }

    /**
     * @param string $current
     * @return string
     * @throws Exception
     */
    public function getGitDiff($current = 'HEAD')
    {
        $this->assert(is_string($current));
        $this->assert(
            preg_match(self::IDENTIFIER_REGEX, $current) > 0,
            "Invalid commit or tag identifier"
        );
        $this->assert(!empty($this->previousIdentifier));
        $this->assert(
            preg_match(self::IDENTIFIER_REGEX, $this->previousIdentifier) > 0,
            "Invalid commit or tag identifier"
        );
        $workingDir = \getcwd();

        // @todo Support more techniques
        if (is_callable('shell_exec')) {
            \chdir($this->directory);
            /**
             * We need to use shell_exec() to get the output of git diff.
             * @psalm-suppress ForbiddenCode
             */
            $result = (string) @shell_exec(
                "git diff {$this->previousIdentifier}..{$current} -G."
            );
            \chdir($workingDir);
            return $result;
        }

        // Failure case: Throw.
        throw new GossamerException("No supported git diff method found");
    }

    /**
     * @param string $outputFile
     * @param string $current
     * @return bool
     * @throws Exception
     */
    public function bundle($outputFile, $current = 'HEAD')
    {
        $diff = $this->getGitDiff($current);
        $this->assert(is_string($diff), "Return type of getGitDiff() must be string");
        return is_int(file_put_contents($outputFile, $diff));
    }
}
