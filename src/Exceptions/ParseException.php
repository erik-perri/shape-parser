<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\Exceptions;

use Exception;
use Sourcetoad\ShapeParser\Data\ParseIssue;

class ParseException extends Exception
{
    /**
     * @param  list<ParseIssue>  $issues
     */
    private function __construct(public readonly array $issues)
    {
        parent::__construct(self::formatMessage($issues));
    }

    public static function fromMessage(string $message): self
    {
        return new self([new ParseIssue([], $message)]);
    }

    /**
     * @param  list<ParseIssue>  $issues
     */
    public static function fromIssues(array $issues): self
    {
        return new self($issues);
    }

    public function withPrefix(string|int $segment): self
    {
        return new self(array_map(
            static fn (ParseIssue $issue): ParseIssue => $issue->withPrefix($segment),
            $this->issues,
        ));
    }

    /**
     * @param  list<ParseIssue>  $issues
     */
    private static function formatMessage(array $issues): string
    {
        if ($issues === []) {
            return 'Failed to parse';
        }

        if (count($issues) === 1 && $issues[0]->path === []) {
            return $issues[0]->message;
        }

        $lines = array_map(
            static fn (ParseIssue $issue): string => sprintf(
                '  at %s: %s',
                self::formatPath($issue->path),
                $issue->message,
            ),
            $issues,
        );

        return "Failed to parse:\n".implode("\n", $lines);
    }

    /**
     * @param  list<string|int>  $path
     */
    private static function formatPath(array $path): string
    {
        if ($path === []) {
            return '<root>';
        }

        return implode('', array_map(
            static fn (string|int $segment): string => '['.$segment.']',
            $path,
        ));
    }
}
