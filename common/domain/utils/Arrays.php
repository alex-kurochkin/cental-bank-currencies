<?php

namespace common\domain\utils;

class Arrays
{

    public static function split(array $array, int $chunkSize = 10): array
    {
        return array_chunk($array, $chunkSize);
    }

    public static function merge(array $array1, array $array2): array
    {
        return array_merge($array1, $array2);
    }

    public static function removeDuplicates(array $array, bool $keepKeys = false): array
    {
        return $keepKeys ? array_unique($array) : array_unique(array_values($array));
    }

    public static function areEqual(array $array1, array $array2): bool
    {
        return empty(array_diff($array1, $array2));
    }

    public static function extractPage(array $items, int $pageSize = 10, int $pageNumber = 1): array
    {
        $pageIndex = $pageNumber - 1;
        $pages = self::split($items, $pageSize);
        return isset($pages[$pageIndex]) ? $pages[$pageIndex] : [];
    }

    public static function removeKeys(array $items, ...$keys): array
    {
        foreach ($keys as $key) {
            unset($items[$key]);
        }

        return $items;
    }

    public static function diff(array $items1, array $items2): array
    {
        return array_diff($items1, $items2);
    }

    public static function subtract(array $items1, array $items2): array
    {
        $result = [];
        foreach ($items1 as $key1 => $value1) {
            $exists = false;
            foreach ($items2 as $key2 => $value2) {
                if (is_numeric($key1) && $value1 == $value2) {
                    $exists = true;
                    break;
                } else if (is_string($key1) && $key1 == $key2) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                continue;
            }

            if (is_numeric($key1)) {
                $result[] = $items1[$key1];

            } else {
                $result[$key1] = $items1[$key1];

            }
        }

        return $result;
    }

    public static function filterKeyIn(array $items, array $keys): array
    {
        $result = [];
        foreach ($items as $key => $value) {
            if (!in_array($key, $keys)) {
                continue;
            }

            $result[$key] = $value;
        }
        return $result;
    }

    public static function toLowerCase(array $values)
    {
        return array_map(function ($value) {
            return Strings::toLowerCase($value);
        }, $values);
    }

    public static function toUpperCase(array $values)
    {
        return array_map(function ($value) {
            return Strings::toUpperCase($value);
        }, $values);
    }

    /**
     * @param array $items
     * @return array
     */
    public static function removeFirst(array $items): array
    {
        return array_shift(array_merge($items, []));
    }

    public static function unique(array $items): array
    {
        return array_unique($items);
    }

    public static function sort(array $items, bool $reverse = false): array
    {
        if ($reverse) {
            rsort($items);
        } else {
            sort($items);
        }

        return $items;
    }
}
