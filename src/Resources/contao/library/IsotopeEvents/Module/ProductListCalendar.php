<?php

namespace IsotopeEvents\Module;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Isotope\Module\ProductList;

/**
 * @property mixed cal_startDay
 */
class ProductListCalendar extends ProductList
{
	/**
	 * Current date object
	 * @var Date
	 */
	protected $Date;

	/**
	 * Current URL
	 * @var string
	 */
	protected $strUrl;

	/**
	 * Template
	 * @var string
	 */
	//protected $strTemplate = 'mod_iso_productlist_cal_mini';

	protected function compile()
	{
		parent::compile ();

		$this->generateCalendar();
	}

	private function generateCalendar()
	{
		// Create the date object
		try
		{
			if (\Input::get('month'))
			{
				$this->Date = new \Date(\Input::get('month'), 'Ym');
			}
			elseif (\Input::get('day'))
			{
				$this->Date = new \Date(\Input::get('day'), 'Ymd');
			}
			else
			{
				$this->Date = new \Date();
			}
		}
		catch (\OutOfBoundsException $e)
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}

		$time = \Date::floorToMinute();

		// Find the boundaries
		$objMinMax = $this->Database->query("SELECT MIN(begin) AS dateFrom, MAX(begin) AS dateTo FROM tl_iso_product WHERE pid = 0" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<='$time') AND (stop='' OR stop>'" . ($time + 60) . "') AND published='1'" : "")); // TODO: limit by categories

		/** @var FrontendTemplate|object $objTemplate */
		$objTemplate = new \FrontendTemplate($this->iso_list_layout);

		// Store year and month
		$intYear = date('Y', $this->Date->tstamp);
		$intMonth = date('m', $this->Date->tstamp);
		$objTemplate->intYear = $intYear;
		$objTemplate->intMonth = $intMonth;

		// Previous month
		$prevMonth = ($intMonth == 1) ? 12 : ($intMonth - 1);
		$prevYear = ($intMonth == 1) ? ($intYear - 1) : $intYear;
		$lblPrevious = $GLOBALS['TL_LANG']['MONTHS'][($prevMonth - 1)] . ' ' . $prevYear;
		$intPrevYm = (int) ($prevYear . str_pad($prevMonth, 2, 0, STR_PAD_LEFT));

		$this->strUrl = preg_replace('/\?.*$/', '', \Environment::get('request'));

		// Only generate a link if there are events (see #4160)
		if (($objMinMax->dateFrom !== null && $intPrevYm >= date('Ym', $objMinMax->dateFrom)) || $intPrevYm >= date('Ym'))
		{
			$objTemplate->prevHref = $this->strUrl . '?month=' . $intPrevYm;
			$objTemplate->prevTitle = \StringUtil::specialchars($lblPrevious);
			$objTemplate->prevLink = $GLOBALS['TL_LANG']['MSC']['cal_previous'] . ' ' . $lblPrevious;
			$objTemplate->prevLabel = $GLOBALS['TL_LANG']['MSC']['cal_previous'];
		}

		// Current month
		$objTemplate->current = $GLOBALS['TL_LANG']['MONTHS'][(date('m', $this->Date->tstamp) - 1)] .  ' ' . date('Y', $this->Date->tstamp);

		// Next month
		$nextMonth = ($intMonth == 12) ? 1 : ($intMonth + 1);
		$nextYear = ($intMonth == 12) ? ($intYear + 1) : $intYear;
		$lblNext = $GLOBALS['TL_LANG']['MONTHS'][($nextMonth - 1)] . ' ' . $nextYear;
		$intNextYm = $nextYear . str_pad($nextMonth, 2, 0, STR_PAD_LEFT);

		// Only generate a link if there are events (see #4160)
		if (($objMinMax->dateTo !== null && $intNextYm <= date('Ym', max($objMinMax->dateTo, $objMinMax->repeatUntil))) || $intNextYm <= date('Ym'))
		{
			$objTemplate->nextHref = $this->strUrl . '?month=' . $intNextYm;
			$objTemplate->nextTitle = \StringUtil::specialchars($lblNext);
			$objTemplate->nextLink = $lblNext . ' ' . $GLOBALS['TL_LANG']['MSC']['cal_next'];
			$objTemplate->nextLabel = $GLOBALS['TL_LANG']['MSC']['cal_next'];
		}

		// Set the week start day
		if (!$this->cal_startDay)
		{
			$this->cal_startDay = 0;
		}

		$objTemplate->days = $this->compileDays();
		$objTemplate->weeks = $this->compileWeeks();
		$objTemplate->substr = $GLOBALS['TL_LANG']['MSC']['dayShortLength'];

		$this->Template->calendar = $objTemplate->parse();
	}

	/**
	 * Return the week days and labels as array
	 *
	 * @return array
	 */
	protected function compileDays()
	{
		$arrDays = array();

		for ($i=0; $i<7; $i++)
		{
			$strClass = '';
			$intCurrentDay = ($i + $this->cal_startDay) % 7;

			if ($i == 0)
			{
				$strClass .= ' col_first';
			}
			elseif ($i == 6)
			{
				$strClass .= ' col_last';
			}

			if ($intCurrentDay == 0 || $intCurrentDay == 6)
			{
				$strClass .= ' weekend';
			}

			$arrDays[$intCurrentDay] = array
			(
				'class' => $strClass,
				'name' => $GLOBALS['TL_LANG']['DAYS'][$intCurrentDay]
			);
		}

		return $arrDays;
	}


	/**
	 * Return all weeks of the current month as array
	 *
	 * @return array
	 */
	protected function compileWeeks()
	{
		$intDaysInMonth = date('t', $this->Date->monthBegin);
		$intFirstDayOffset = date('w', $this->Date->monthBegin) - $this->cal_startDay;

		if ($intFirstDayOffset < 0)
		{
			$intFirstDayOffset += 7;
		}

		$intColumnCount = -1;
		$intNumberOfRows = ceil(($intDaysInMonth + $intFirstDayOffset) / 7);
		$arrAllEvents = $this->getAllEvents($this->cal_calendar, $this->Date->monthBegin, $this->Date->monthEnd); // TODO: Override this with products
		$arrDays = array();

		// Compile days
		for ($i=1; $i<=($intNumberOfRows * 7); $i++)
		{
			$intWeek = floor(++$intColumnCount / 7);
			$intDay = $i - $intFirstDayOffset;
			$intCurrentDay = ($i + $this->cal_startDay) % 7;

			$strWeekClass = 'week_' . $intWeek;
			$strWeekClass .= ($intWeek == 0) ? ' first' : '';
			$strWeekClass .= ($intWeek == ($intNumberOfRows - 1)) ? ' last' : '';

			$strClass = ($intCurrentDay < 2) ? ' weekend' : '';
			$strClass .= ($i == 1 || $i == 8 || $i == 15 || $i == 22 || $i == 29 || $i == 36) ? ' col_first' : '';
			$strClass .= ($i == 7 || $i == 14 || $i == 21 || $i == 28 || $i == 35 || $i == 42) ? ' col_last' : '';

			// Empty cell
			if ($intDay < 1 || $intDay > $intDaysInMonth)
			{
				$arrDays[$strWeekClass][$i]['label'] = '&nbsp;';
				$arrDays[$strWeekClass][$i]['class'] = 'days empty' . $strClass;
				$arrDays[$strWeekClass][$i]['events'] = array();

				continue;
			}

			$intKey = date('Ym', $this->Date->tstamp) . ((\strlen($intDay) < 2) ? '0' . $intDay : $intDay);
			$strClass .= ($intKey == date('Ymd')) ? ' today' : '';

			// Mark the selected day (see #1784)
			if ($intKey == \Input::get('day'))
			{
				$strClass .= ' selected';
			}

			// Inactive days
			if (empty($intKey) || !isset($arrAllEvents[$intKey]))
			{
				$arrDays[$strWeekClass][$i]['label'] = $intDay;
				$arrDays[$strWeekClass][$i]['class'] = 'days' . $strClass;
				$arrDays[$strWeekClass][$i]['events'] = array();

				continue;
			}

			$arrEvents = array();

			// Get all events of a day
			foreach ($arrAllEvents[$intKey] as $v)
			{
				foreach ($v as $vv)
				{
					$arrEvents[] = $vv;
				}
			}

			$arrDays[$strWeekClass][$i]['label'] = $intDay;
			$arrDays[$strWeekClass][$i]['class'] = 'days active' . $strClass;
			$arrDays[$strWeekClass][$i]['href'] = $this->strLink . '?day=' . $intKey;
			$arrDays[$strWeekClass][$i]['title'] = sprintf(\StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['cal_events']), \count($arrEvents));
			$arrDays[$strWeekClass][$i]['events'] = $arrEvents;
		}

		return $arrDays;
	}
}