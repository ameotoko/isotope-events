<?php
/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Isotope\Backend\Product;

use Isotope\Backend\Product\DcaManager;

class DcaManagerCustom extends DcaManager
{
	public function publishNewVariant($insertID)
	{
		\Database::getInstance()
			->prepare("UPDATE tl_iso_product SET published='1' WHERE pid=?")
			->execute($insertID)
		;
	}
}
