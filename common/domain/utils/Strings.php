<?php

namespace common\domain\utils;

use DateTime;

class Strings
{

    const ARRAY_SEPARATOR = '; ';

    public static function isBlank(string $value): bool
    {
        return empty(trim($value));
    }

    public static function removeStart(string $value, string $prefix): string
    {
        if (substr($value, 0, strlen($prefix)) == $prefix) {
            return substr($value, strlen($prefix));
        }

        return $value;
    }

    public static function fromDate(DateTime $date, string $format = DateTimes::DATE_FORMAT): string
    {
        return $date->format($format);
    }

    public static function fromDateTime(DateTime $date, string $format = DateTimes::DATETIME_FORMAT): string
    {
        return $date->format($format);
    }

    /**
     * @param string $format
     * @param array  $values
     * @return string
     */
    public static function format(string $format, ...$values): string
    {
        // replace placeholders with positions
        $index = -1;
        $format = preg_replace_callback('/\{(\d*)\}/i', function ($match) use ($values, &$index) {
            // increment index
            ++$index;

            // determine replacement index for current match
            $replacementIndex = $match[1] != '' ? (int)$match[1] : $index;

            // return replacement
            $value = $values[$replacementIndex] ?? $match[1];
            if (is_array($value)) {
                return join(', ', $value);
            }

            // system types without __toString()
            if ($value instanceof DateTime) {
                return self::fromDateTime($value);
            }

            return $value;
        }, $format);

        return $format;
    }

    public static function startsWith(string $value, string $substring): bool
    {
        return substr($value, 0, strlen($substring)) === $substring;
    }

    public static function toUpperCase(string $value): string
    {
        return mb_strtoupper($value);
    }

    public static function toLowerCase(string $value): string
    {
        return mb_strtolower($value);
    }

    public static function fromArray(array $items, bool $associative = false): string
    {
        if (!$associative) {
            return join(self::ARRAY_SEPARATOR, $items);
        }

        $result = '';
        foreach ($items as $key => $value) {
            if ($result) {
                $result .= self::ARRAY_SEPARATOR;
            }

            $result .= "{$key} = {$value}";
        }
        return $result;
    }
}
