<?php
/**
 * LanguageCode
 */
namespace Tests\Models;

/**
 * LanguageCode
 */
class LanguageCode
{
    /**
     * Possible values of this enum
     */
    const EN = 'en';

    const GR = 'gr';

    const DE = 'de';

    const IT = 'it';

    const ES = 'es';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::EN,
            self::GR,
            self::DE,
            self::IT,
            self::ES
        ];
    }
}
