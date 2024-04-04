<?php

/**
 * Base pattern class.
 *
 * @package P4\MasterTheme
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Base_Pattern
 *
 * @package P4\MasterTheme\Patterns
 */
abstract class BlockPattern
{
    /**
     * Returns the pattern name.
     */
    abstract public static function get_name(): string;

    /**
     * Returns the pattern config.
     *
     * @param array $params Optional array of parameters for the config.
     */
    abstract public static function get_config(array $params = []): array;

    /**
     * Returns the pattern classname used for tracking patterns.
     */
    public static function get_classname(): string
    {
        return self::make_classname(static::get_name());
    }

    /**
     * Patterns list.
     */
    public static function get_list(): array
    {
        return [
            SideImageWithTextAndCta::class,
        ];
    }

    /**
     * Pattern constructor.
     */
    public static function register_all(): void
    {
        if (! function_exists('register_block_pattern')) {
            return;
        }

        $patterns = self::get_list();

        /**
         * @var $pattern self
         */
        foreach ($patterns as $pattern) {
            register_block_pattern($pattern::get_name(), $pattern::get_config());
        }
    }

    /**
     * @param string $name Pattern name.
     */
    private static function make_classname(string $name): string
    {
        return 'is-pattern-' . preg_replace('#[^_a-zA-Z0-9-]#', '-', $name);
    }
}
