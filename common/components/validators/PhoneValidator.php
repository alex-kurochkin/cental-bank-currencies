<?php

namespace common\components\validators;

use Exception;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use yii\validators\Validator;

/**
 * Phone validator class that validates phone numbers for given country and formats.
 * Country codes and attributes value should be ISO 3166-1 alpha-2 codes
 *
 * Examples of phones:
 * '+79041230123'      // valid
 * '+7 904 123 0 123'  // valid
 * '+7904 12-30-123'   // valid
 * '+7 904 12 30 12 3' // valid
 * '79041230123'       // invalid
 * '+89041230123'      // invalid
 * '+7904123012'       // invalid
 * '+71111111111'      // invalid
 * '+79041111111'      // valid
 */
class PhoneValidator extends Validator
{

    /**
     * Whether to remove plus
     *
     * @var bool
     */
    public $removePlus = true;
    /**
     * If phone number is valid formats value with libphonenumber/PhoneNumberFormat const (default to E164)
     *
     * If is bool value:
     *  - true - enable formatting as E164 (see case "is int value")
     *  - false - disable formatting. Phone will remain as is
     *
     * If is int value:
     *
     * INTERNATIONAL and NATIONAL formats are consistent with the definition in ITU-T Recommendation
     * E123.
     * For example, the number of the Google Switzerland office will be written as
     * "+41 44 668 1800" in INTERNATIONAL format, and as "044 668 1800" in NATIONAL format.
     * E164 format is as per INTERNATIONAL format but with no formatting applied, e.g.
     * "+41446681800". RFC3966 is as per INTERNATIONAL format, but with all spaces and other
     * separating symbols replaced with a hyphen, and with any phone number extension appended with
     * ";ext=". It also will have a prefix of "tel:" added, e.g. "tel:+41-44-668-1800".
     *
     * PhoneNumberFormat
     * {
     *   const E164 = 0;          // "+41446681800"
     *   const INTERNATIONAL = 1; // "+41 44 668 1800"
     *   const NATIONAL = 2;      // "044 668 1800"
     *   const RFC3966 = 3;       // "tel:+41-44-668-1800"
     * }
     *
     * @var bool|int
     */
    public $format = true;
    /** @inheritdoc */
    public $message = '{attribute} is not a valid phone number';

    /** @inheritdoc */
    public function validateAttribute($model, $attribute)
    {
        if ($this->format === true) {
            $this->format = PhoneNumberFormat::INTERNATIONAL;
        }
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phone = $this->changeLocalPrefix('+' . ltrim($model->$attribute, '+'));

            $numberProto = $phoneUtil->parse($phone);
            if ($phoneUtil->isValidNumber($numberProto)) {
                if (is_numeric($this->format)) {
                    $phoneFormatted = $phoneUtil->format($numberProto, $this->format);
                    $model->$attribute = $this->removePlus ? ltrim($phoneFormatted, '+') : $phoneFormatted;
                }
            } else {
                $this->addError($model, $attribute, $this->message);
            }
        } catch (Exception $e) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /**
     * @param string $phone
     *
     * @return string
     */
    protected function changeLocalPrefix(string $phone): string
    {
        $local = [
            '+89' => '+78',
        ];

        return str_replace(array_keys($local), array_values($local), $phone);
    }
}
