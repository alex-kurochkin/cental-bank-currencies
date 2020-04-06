<?php

namespace common\components;

use common\components\exceptions\Exception;
use DateTime;
use InvalidArgumentException;
use Yii;

/** Contains various methods that can be used universally */
class Tools
{

    /**
     * Parses a string like "1,4,5-10,2,13-20". Negative values are allowed: '-10--5' will produce [-10,-9,-8,-7,-6,-5]
     *
     * @param string $str Input string
     *
     * @return array array of all integer values found, e.g. [1,4,5,6,7,8,9,10,2,13,14,15,16,17,18,19,20]
     */
    public static function parseNumericRange($str)
    {
        $str = filter_var($str, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/[0-9\-,]/']]);
        if (empty($str)) {
            return [];
        }
        $parts = preg_split('/,/', $str, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($parts)) {
            return [];
        }
        $result = [];
        for ($i = 0, $l = count($parts); $i < $l; $i++) {
            $part = $parts[$i];
            $matches = [];
            if (preg_match('/(-{0,1}[0-9]+)-(-{0,1}[0-9]+)/', $part, $matches)) {
                $start = intval($matches[1]);
                $end = intval($matches[2]);
                if (!is_int($start) || !is_int($end) || $start > $end) {
                    continue;
                }
                $result = array_merge($result, range($start, $end));
            } else {
                if (preg_match('/^-{0,1}[0-9]+$/', $part, $matches)) {
                    $value = intval($matches[0]);
                    if (!is_int($value)) {
                        continue;
                    }
                    $result[] = $value;
                } else {
                    continue;
                }
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * Generates a UUID version 4
     *
     * If openssl is available, uses it, otherwise generates only if app is in debug mode
     * @return bool|string false if not in debug mode and openssl is not available, otherwise a UUID v4 string
     * @throws Exception If openssl is not enabled
     */
    public static function generateUUID(bool $delimiter = false)
    {
        if (extension_loaded('openssl')) {
            $data = openssl_random_pseudo_bytes(16, $strong);
            if ($strong === false) {
                throw new Exception('Openssl has no access to strong algorithms');
            }
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
            $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        } else {
            if (YII_DEBUG) {
                return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
            } else {
                throw new Exception('Openssl is not available');
            }
        }

        return $delimiter ? $uuid : str_replace('-', '', $uuid);
    }

    /**
     * Checks whether a string is a valid UUID v4
     *
     * @param string $string
     * @param bool $delimiter
     *
     * @return bool
     */
    function isUuid(string $string, bool $delimiter = false): bool
    {
        if ($delimiter) {
            return preg_match('/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/', $string) === 1;
        } else {
            return preg_match('/^[\da-f]{8}([\da-f]{4}){3}[\da-f]{12}$/', $string) === 1;
        }
    }

    /**
     * Calculates difference between two dates in days
     * Dates can be either DateTime objects or string representations
     *
     * @param DateTime|string $dateFrom
     * @param DateTime|string $dateUntil
     * @param boolean $roundDown (optional) Whether to round down, instead of up by default
     *
     * @return int amount of days (can be negative)
     */
    public static function dateDiffDays($dateFrom, $dateUntil, $roundDown = false)
    {
        if ($roundDown) {
            return floor(static::dateDiff($dateFrom, $dateUntil) / 86400);
        } else {
            return ceil(static::dateDiff($dateFrom, $dateUntil) / 86400);
        }
    }

    /**
     * Calculates difference between two dates in seconds
     * Dates can be either DateTime objects or string representations
     *
     * @param DateTime|string $dateFrom
     * @param DateTime|string $dateUntil
     *
     * @return bool|int amount of seconds (can be negative) or false if any date is malformed
     */
    public static function dateDiff($dateFrom, $dateUntil)
    {
        if (!is_a($dateFrom, 'DateTime')) {
            try {
                $dateFrom = new DateTime($dateFrom);
            } catch (\Exception $e) {
                return false;
            }
        }
        if (!is_a($dateUntil, 'DateTime')) {
            try {
                $dateUntil = new DateTime($dateUntil);
            } catch (\Exception $e) {
                return false;
            }
        }

        return $dateUntil->getTimestamp() - $dateFrom->getTimestamp();
    }

    /**
     * Fix Year 2038 problem
     *
     * @link https://en.wikipedia.org/wiki/Year_2038_problem
     *
     * @param int|string|null $date
     *
     * @return int|string|null
     */
    public static function dateEndUnixTimeFix($date)
    {
        if ($date === null) {
            return null;
        }
        $str2038 = '2038-01-01 00:00:01';
        $date2038 = strtotime($str2038);

        $timeStamp = self::timestamp2Unix($date);

        if ($timeStamp && $timeStamp > $date2038) {
            $date = is_numeric($date) && (int)$date == $date ? $date2038 : $str2038;
        }

        return $date;
    }

    /**
     * Parses a scalar array of options, leaving only options which correspond to 1 in bit-representation of the $value
     * parameter
     *
     * @param int $value Integer representation of a bit string, where ones correspond to enabled options
     * @param array $options Scalar array of options (['a','b','c',...])
     *
     * @return array|bool
     *
     * @throws InvalidArgumentException
     */
    public static function parseBitFlagOptions($value, $options)
    {
        if (!is_int($value) || !is_array($options)) {
            throw new InvalidArgumentException();
        }
        $i = 0;
        $result = [];
        while (true && $i < 512) {
            $mask = pow(2, $i);
            if ($mask > $value) {
                break;
            }
            $selected = $value & $mask;
            if ($selected) {
                $result[] = $options[$i];
            }
            $i++;
        }

        return $result;
    }

    /**
     * Randomly generates a string in base64 using a cryptographically strong algorithm.
     *
     * @param int $length Amount of random symbols
     *
     * @return string
     */
    public static function genCryptoRandomString($length)
    {
        if (!is_int($length) || $length <= 0) {
            throw new InvalidArgumentException();
        }
        $bLength = ceil($length / 0.75);
        $bytes = openssl_random_pseudo_bytes($bLength);
        $string = strtr(substr(base64_encode($bytes), 0, $length), '+/', '-_');

        return $string;
    }

    /**
     * Randomly generates a string. Uses alphanumerical symbols, "-" and "_" to create a base64 string.
     * The alphabet will start with numbers, go through lowercase and uppercase letters and finish with -_, so
     * that if you specify base 13, you'll get numbers 0 through 9, a,b and c.
     *
     * @param int $length Amount of random symbols
     * @param int $base Symbols in the alphabet
     * @param string $alphabet Set custom alphabet
     *
     * @return string
     */
    public static function genRandomStr($length = 8, $base = 64, $alphabet = '')
    {
        $alphabet = $alphabet ?: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
        if (!is_int($length) || $length <= 0 || $base <= 0 || $base > 64 || !is_string($alphabet) || empty($alphabet)) {
            throw new InvalidArgumentException();
        }
        $chars = substr($alphabet, 0, $base);
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[mt_rand(0, $numChars - 1)];
        }

        return $string;
    }

    /**
     * For two-dimensional arrays injects given properties into each sub-array
     * e.g If given target [['a'=>1,'b'=>2],['c'=>5,'d'=>10]] and properties ['e'=>15,'d'=>30], result will be
     * [['a'=>1,'b'=>2,'e'=>15,'d'=>30],['c'=>5,'d'=>30,'e'=>15]]
     *
     * @param array $target 2D array
     * @param array $injection Data to be injected e.g. ['c'=>3,'d'=>4]
     */
    public static function arrayInject(array &$target, array $injection)
    {
        array_walk($target, function (array &$array, $key, $properties) {
            $array = array_merge($array, $properties);
        }, $injection);
    }

    /**
     * Determines whether all fields of array A are the same as in array B.
     * If there are fields in B that are not present in A, ignores it.
     * For fields with array values, performs the same check recursively
     *
     * @param array $a Needle
     * @param array $b Array, against which we will check
     * @param bool $allowSubarray If set to true, will search all levels of $b for $a
     * @param bool $strict Whether to perform strict type comparison
     *
     * @return bool
     */
    public static function arrayContained(array $a, array $b, $allowSubarray = true, $strict = false)
    {
        if ($allowSubarray) {
            $subarrays = array_filter($b, function ($item) {
                return is_array($item);
            });
            $foundInSubarray = false;
            foreach ($subarrays as $subarray) {
                $foundInSubarray |= static::arrayContained($a, $subarray, $allowSubarray, $strict);
            }
            if ($foundInSubarray) {
                return true;
            }
        }
        foreach ($a as $key => $value) {
            if (!isset($b[$key])) { //If there is no key like this, quit
                return false;
            }
            if (is_array($b[$key])) { // Recursion
                if (!is_array($value)) {
                    return false;
                }
                if (!static::arrayContained($value, $b[$key], $allowSubarray, $strict)) {
                    return false;
                }
                continue;
            }
            if (is_int($key) && in_array($value, $b)) {
                continue;
            }
            if ($value != $b[$key] || ($value !== $b[$key] && $strict)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Merges two array in such a way that their values combine into arrays. Behaves in the same way as
     * array_merge_recursive, but forces all resulting values to be arrays, even if value with that key exists only in
     * one of arrays
     *
     * @param array $a
     * @param array $b
     *
     * @return array Resulting array
     */
    public static function arrayMergeToArrays(array $a, array $b)
    {
        $result = [];
        foreach ($a as $key => $value) {
            $result[$key] = [$value];
        }
        foreach ($b as $key => $value) {
            if (!isset($result[$key])) {
                $result[$key] = [];
            }
            $result[$key][] = $value;
        }

        return $result;
    }

    /**
     * Get the path to php file on behalf  the namespace
     *
     * @param string $namespace
     *
     * @return string Path to php file
     */
    public static function namespace2path($namespace)
    {
        return realpath(Yii::getAlias('@' . str_replace('\\', '/', $namespace)) . '.php');
    }

    /**
     * Get the namespace of class on behalf the path to php file
     *
     * @param string $path
     *
     * @return string Path to php file
     */
    public static function path2namespace($path)
    {
        $basePathNS = str_replace('\\', '/', Yii::$app->basePath);
        $path = str_replace([$basePathNS, '.php'], '', trim($path, '\\'));

        return trim(str_replace('/', '\\', $path), '\\');
    }

    /**
     * Encode unix time to timestamp
     *
     * @param int $unixTime Unix time
     *
     * @return string Formatted date
     */
    public static function unix2Timestamp($unixTime)
    {
        return date('Y-m-d H:i:s', self::timestamp2Unix($unixTime));
    }

    /**
     * Decode timestamp to unix time
     *
     * @param int|string $timestamp Timestamp string
     *
     * @return int Unix time
     */
    public static function timestamp2Unix($timestamp): ?int
    {
        if (!is_numeric($timestamp)) {
            $timestamp = strtotime($timestamp);
        }

        return (int)$timestamp;
    }

    /**
     * Returns indexes of row with certain values.
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     *
     * @return array Index
     */
    public static function getValuesIndexes(array $array, $key, $value)
    {
        $result = [];
        foreach ($array as $k => $v) {
            if ($v[$key] == $value) {
                $result[] = $k + 1;
            }
        }

        return $result;
    }

    /**
     * Return array arrays get by key inside array
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     *
     * @return array
     */
    public static function getArraysByKey(array $array, $key, $value)
    {
        $arr = [];
        foreach ($array as $k => $v) {
            if ($v[$key] == $value) {
                $arr[] = $v;
            }
        }

        return $arr;
    }

    /**
     * Returns array without duplicated keys values rows.
     *
     * @param array $data
     * @param array $keys
     *
     * @return array
     */
    public static function arrayUniqueByKeys(array $data, array $keys)
    {
        $result = [];
        $sliced = [];
        foreach ($data as $index => $row) {
            $assembled = [];
            foreach ($keys as $key) {
                $assembled[$key] = $row[$key];
            }
            $sliced[$index] = $assembled;
        }
        $sliced = array_map("unserialize", array_unique(array_map("serialize", $sliced)));
        foreach ($sliced as $index => $row) {
            $result[] = $data[$index];
        }

        return $result;
    }

    /**
     * Returns only [a-z_-] and underscore symbol instead of spaces.
     *
     * @param $value
     *
     * @return string Normalized as url value
     */
    public static function normalizeAsUrl($value)
    {
        return preg_replace('/[^a-z_-]/i', '', str_replace(' ', '_', strtolower($value)));
    }

    /**
     * Returns string as float, throw deleting commas.
     *
     * @param $string
     *
     * @return float
     */
    public static function toFloat($string)
    {
        return filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Truncates string.
     *
     * Usage example:
     *
     *      Tools::truncate('строка строка строка',10);                    | return "строка..."
     *      Tools::truncate('строка строка строка',10,'...', true);        | return "строка с..."     *
     *
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param bool $break_words
     *
     * @return string
     */
    static public function truncate($string, $length = 100, $etc = '...', $break_words = false)
    {
        if ($length == 0) {
            return '';
        }
        $chars = 'UTF-8';
        $string = trim($string);
        if (mb_strlen($string, $chars) > $length) {
            $length -= min($length, mb_strlen($etc, $chars));

            if (!$break_words) {
                $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length + 1, $chars));
            }

            if (substr_count($string, ' ') < 2 and $length > 20) {
                $string = substr_replace($string, ' ', $length / 2, 0);
            }

            $string = trim(mb_substr($string, 0, $length, $chars)) . $etc;
        }

        return $string;
    }
}
