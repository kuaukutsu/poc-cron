<?php

declare(strict_types=1);

namespace kuaukutsu\poc\cron\tools;

use Stringable;

/**
 * @psalm-immutable
 */
final class ProcessUuid implements Stringable
{
    /**
     * @var non-empty-string
     */
    private readonly string $uuid;

    /**
     * @param non-empty-string $name
     */
    public function __construct(private readonly string $name)
    {
        $this->uuid = $this->generateUuid($this->name);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->uuid;
    }

    /**
     * @return non-empty-string
     */
    private function generateUuid(string $name): string
    {
        /**
         * @var non-empty-string
         */
        return preg_replace(
            '~^(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})$~',
            '\1-\2-\3-\4-\5',
            hash('md5', $name)
        );
    }
}
