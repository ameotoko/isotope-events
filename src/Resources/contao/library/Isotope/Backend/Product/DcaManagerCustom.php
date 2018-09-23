<?php
/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Isotope\Backend\Product;



class DcaManagerCustom extends DcaManager
{
	/**
	 * Publish variants by default when copying a product
	 *
	 * @param $insertID
	 */
	public function publishNewVariant($insertID)
	{
		\Database::getInstance()
			->prepare("UPDATE tl_iso_product SET published='1' WHERE pid=?")
			->execute($insertID)
		;
	}

	/**
	 * Clean up variants in case of cancelled copying of product
	 *
	 * @param       $strTable
	 * @param array $newRecords
	 *
	 * @return bool
	 */
	public function onReviseTable($strTable, $newRecords)
	{
		if ('tl_iso_product' == $strTable) {
			foreach ($newRecords as $newRecord) {
				\Database::getInstance()->prepare("DELETE FROM tl_iso_product WHERE pid=?")->execute($newRecord);
			}
		}

		return false;
	}
}
