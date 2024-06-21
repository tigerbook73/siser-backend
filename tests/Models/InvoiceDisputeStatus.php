<?php
/**
 * InvoiceDisputeStatus
 */
namespace Tests\Models;

/**
 * InvoiceDisputeStatus
 */
class InvoiceDisputeStatus
{
    /**
     * Possible values of this enum
     */
    const NONE = 'none';

    const DISPUTING = 'disputing';

    const DISPUTED = 'disputed';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NONE,
            self::DISPUTING,
            self::DISPUTED
        ];
    }
}
