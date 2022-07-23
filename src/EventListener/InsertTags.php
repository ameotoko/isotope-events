<?php

/**
 * @author Andrey Vinichenko <andrey.vinichenko@gmail.com>
 */

namespace Ameotoko\IsotopeEvents\EventListener;

use Contao\Date;

class InsertTags
{
    public static function replaceInsertTags($strTag, $blnCache = true)
    {
        $arrSplit = explode('::', $strTag);

        if ($arrSplit[0] != 'nextMonth' && $arrSplit[0] != 'cache_nextMonth') {
            return false;
        }

        // would be the most elegant, but is not reliable. Returns 'October 2017' from '2017/08/31'
        // return date('F Y', strtotime('next month'));
        $arrCurrentDate = getdate();
        return Date::parse('F Y', mktime(0, 0, 0, $arrCurrentDate['mon'] + 1));
    }
}
