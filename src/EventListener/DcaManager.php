<?php
/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Doctrine\DBAL\Connection;

class DcaManager
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Publish variants by default when copying a product
     *
     * @Callback(table="tl_iso_product", target="config.oncopy")
     */
    public function publishNewVariant(int $insertID): void
    {
        $this->connection->update('tl_iso_product', ['published' => '1'], ['pid' => $insertID]);
    }

    /**
     * Clean up variants in case of cancelled copying of product
     *
     * @Hook("reviseTable")
     */
    public function onReviseTable(string $strTable, ?array $newRecords): bool
    {
        if ('tl_iso_product' == $strTable && is_array($newRecords)) {
            foreach ($newRecords as $newRecord) {
                $this->connection->delete('tl_iso_product', ['pid' => $newRecord]);
            }
        }

        return false;
    }
}
