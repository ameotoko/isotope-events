<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Ameotoko\IsotopeEvents\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class UppercaseLocationCountries extends AbstractMigration
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Convert country codes to uppercase for contao.intl.countries service';
    }

    public function shouldRun(): bool
    {
        return 0 < $this->connection
            ->fetchOne("select count(country) from tl_iso_location where country regexp binary '[a-z]+'");
    }

    public function run(): MigrationResult
    {
        $this->connection
            ->executeStatement("update tl_iso_location set country=upper(country) where country regexp binary '[a-z]+'");

        return $this->createResult(true);
    }
}
