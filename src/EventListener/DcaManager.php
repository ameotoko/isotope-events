<?php
/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\Database;

class DcaManager
{
    /**
     * Publish variants by default when copying a product
     */
    public function publishNewVariant(int $insertID): void
    {
        Database::getInstance()
            ->prepare("UPDATE tl_iso_product SET published='1' WHERE pid=?")
            ->execute($insertID)
        ;
    }

    /**
     * Clean up variants in case of cancelled copying of product
     */
    public function onReviseTable(string $strTable, ?array $newRecords): bool
    {
        if ('tl_iso_product' == $strTable && is_array($newRecords)) {
            foreach ($newRecords as $newRecord) {
                Database::getInstance()->prepare("DELETE FROM tl_iso_product WHERE pid=?")->execute($newRecord);
            }
        }

        return false;
    }
}
