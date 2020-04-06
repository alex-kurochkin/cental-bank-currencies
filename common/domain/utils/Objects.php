<?php

namespace common\domain\utils;

use InvalidArgumentException;

class Objects
{

    public static function equals($value1, $value2): bool
    {
        if (is_array($value1) && is_array($value2)) {
            foreach ($value1 as $key => $value) {
                if (!isset($value2[$key]) || !self::equals($value, $value2[$key])) {
                    return false;
                }
            }

        } else if (is_object($value1) && is_object($value2)) {
            foreach ($value1 as $key => $value) {
                if (!isset($value2->$key) || !self::equals($value, $value2->$key)) {
                    return false;
                }
            }
        }

        return $value1 == $value2;
    }

    /**
     * Cast object to class
     *
     * @param mixed  $object
     * @param string $className
     *
     * @return mixed
     */
    public static function cast($object, string $className)
    {
        // phpcs:ignore
        $class = self::class;

        if (!is_object($object)) {
            throw new InvalidArgumentException('$object must be an object.');
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf('Unknown class: %s.', $className));
        }

        if (!is_subclass_of($className, get_class($object))) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a descendant of $object class: %s.',
                $className,
                get_class($object)
            ));
        }

        /**
         * This is a beautifully ugly hack.
         *
         * First, we serialize our object, which turns it into a string, allowing
         * us to muck about with it using standard string manipulation methods.
         *
         * Then, we use preg_replace to change it's defined type to the class
         * we're casting it to, and then serialize the string back into an
         * object.
         */

        return unserialize(
            preg_replace(
                '/^O:\d+:"[^"]++"/',
                'O:' . strlen($className) . ':"' . $className . '"',
                serialize($object)
            )
        );
    }

}
