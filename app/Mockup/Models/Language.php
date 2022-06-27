<?php
/**
 * Language
 */
namespace App\Mockup\Models;

/**
 * Language
 */
class Language
{
    /**
     * Possible values of this enum
     */
    const ENGLISH = 'English';

    const FRENCH = 'French';

    const GERMAN = 'German';

    const ITALIAN = 'Italian';

    const SPANISH = 'Spanish';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ENGLISH,
            self::FRENCH,
            self::GERMAN,
            self::ITALIAN,
            self::SPANISH
        ];
    }
}
