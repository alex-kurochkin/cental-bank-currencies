<?php

namespace common\domain\utils;

use DateTime;
use DateInterval;
use DateTimeZone;

class DateTimes
{

    const DATE_FORMAT = 'Y-m-d';
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const ISO_FORMAT = 'c';

    /**
     * Creates DateTime from yyyy-mm-dd string
     *
     * @param string|null $value
     * @param string      $format
     * @return DateTime|null
     */
    public static function fromDateString(?string $value, string $format = self::DATE_FORMAT): ?DateTime
    {
        if ($value == null) {
            return null;
        }

        $dateTime = DateTime::createFromFormat($format, $value);
        if (!$dateTime) {
            return null;
        }

        $dateTime->setTime(0, 0);
        return $dateTime;
    }

    /**
     * Creates DateTime from yyyy-mm-dd HH:mm:ss string
     *
     * @param string|null $value
     * @param string      $format
     * @return DateTime|null
     */
    public static function fromDateTimeString(?string $value, string $format = self::DATETIME_FORMAT): ?DateTime
    {
        if ($value == null) {
            return null;
        }

        $dateTime = DateTime::createFromFormat($format, $value, new DateTimeZone("UTC"));
        if (!$dateTime) {
            return null;
        }

        return $dateTime;
    }

    /**
     * Creates DateTime from unix timestamp
     *
     * @param int $unixTimestamp
     * @return DateTime|null
     * @throws \Exception
     */
    public static function fromUnixTimestamp(int $unixTimestamp): DateTime
    {
        return new DateTime("@$unixTimestamp");
    }

    public static function addDays(int $days, ?DateTime $date = null): DateTime
    {
        if (!$date) {
            $date = new DateTime();
        }

        $date->add(new DateInterval("P{$days}D"));
        return $date;
    }

    public static function now(): DateTime
    {
        return DateTime::createFromFormat('U', (string) time());
    }

    public static function today(): DateTime
    {
        $today = new DateTime();
        $today->setTime(0, 0);
        return $today;
    }

    /**
     * @param int $minutes
     * @return DateTime
     * @throws \Exception
     */
    public static function nowPlusMinutes(int $minutes = 0): DateTime
    {
        $timestamp = strtotime(sprintf('+%s minute', $minutes));
        return self::fromUnixTimestamp($timestamp);
    }

    public static function todayPlusYears(int $years)
    {
        $today = self::today();
        $today->add(new DateInterval("P{$years}Y"));
        return $today;
    }

    public static function todayPlusDays(int $days)
    {
        $today = self::today();
        $today->add(new DateInterval("P{$days}D"));
        return $today;
    }
}
