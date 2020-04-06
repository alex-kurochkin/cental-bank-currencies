<?php

namespace common\domain\utils;

class ErrorMessageBuilder
{

    public static function build(array $errors): string
    {
        if (empty($errors)) {
            return '';
        }

        /**
         * @var string[] $propertyErrors
         */
        $messages = [];
        foreach ($errors as $propertyName => $propertyErrors) {
            $messages[] = join($propertyErrors, "\n");
        }

        return join("\n", $messages);
    }
}
