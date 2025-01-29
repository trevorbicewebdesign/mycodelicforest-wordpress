<?php

class ConsoleColors
{
    public const RESET = "\033[0m";
    public const RED = "\033[31m";
    public const GREEN = "\033[32m";
    public const YELLOW = "\033[33m";
    public const BLUE = "\033[34m";
    public const MAGENTA = "\033[35m";
    public const CYAN = "\033[36m";
    public const WHITE = "\033[37m";

    /**
     * Helper: wrap a string in the specified color, then reset.
     *
     * @param string $text
     * @param string $color
     * @return string
     */
    public static function colorize(string $text, string $color): string
    {
        return $color . $text . self::RESET;
    }
}

