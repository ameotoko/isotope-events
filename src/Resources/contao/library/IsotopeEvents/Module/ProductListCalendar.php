<?php

namespace IsotopeEvents\Module;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Haste\Http\Response\HtmlResponse;
use Isotope\Module\ProductList;

/**
 * @property mixed       cal_startDay
 * @property array|mixed arrEvents
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

	protected $arrEvents;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_productlist_cal_mini';

	/**
	 * Cache products. Can be disable in a child class, e.g. a "random products list"
	 * @var boolean
	 *
	 * @deprecated Deprecated since version 2.3, to be removed in 3.0.
	 *             Implement getCacheKey() to always cache result.
	 */
	protected $blnCacheProducts = false;
	
	public function generate()
	{
		if ($this->asCalendar) {
			$this->type = 'event_calendar';
		}

		return parent::generate ();
	}

	protected function compile()
	{
		parent::compile ();
//dump ($this->Template->products);
		if ($this->asCalendar) {
			$this->generateCalendar ();
		}
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
		$objTemplate = new \FrontendTemplate('iso_list_cal_mini');

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
		$this->Template->id = $this->id;

		if (\Environment::get('isAjaxRequest') && \Input::post('FORM_SUBMIT') == 'calendar') {
			$objResponse = new HtmlResponse($this->Template->calendar);
			$objResponse->send();
		}
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

		$arrAllEvents = $this->getAllEvents($this->Date->monthBegin, $this->Date->monthEnd); // TODO: Override this with products
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

	/**
	 * Get all events of a certain period (original: calendar-bundle/Events)
	 *
	 * @param array   $arrCalendars
	 * @param integer $intStart
	 * @param integer $intEnd
	 *
	 * @return array
	 */
	protected function getAllEvents($intStart, $intEnd)
	{
		//if (!\is_array($arrCalendars))
		//{
		//	return array();
		//}
		//
		$this->arrEvents = array();

		foreach ($this->Template->products as $arrEvent)
		{
			// Get the events of the current period
			//$objEvents = \CalendarEventsModel::findCurrentByPid($id, $intStart, $intEnd);

			//if ($objEvents === null)
			//{
			//	continue;
			//}

			//while ($objEvents->next())
			//{
			$objEvent = $arrEvent['product'];
				$this->addEvent($objEvent, $objEvent->begin, ($objEvent->end) ?: $objEvent->begin, $intStart, $intEnd, $id);

				// Recurring events
				//if ($objEvents->recurring)
				//{
				//	$arrRepeat = \StringUtil::deserialize($objEvents->repeatEach);
				//
				//	if (!\is_array($arrRepeat) || !isset($arrRepeat['unit']) || !isset($arrRepeat['value']) || $arrRepeat['value'] < 1)
				//	{
				//		continue;
				//	}
				//
				//	$count = 0;
				//	$intStartTime = $objEvents->startTime;
				//	$intEndTime = $objEvents->endTime;
				//	$strtotime = '+ ' . $arrRepeat['value'] . ' ' . $arrRepeat['unit'];
				//
				//	while ($intEndTime < $intEnd)
				//	{
				//		if ($objEvents->recurrences > 0 && $count++ >= $objEvents->recurrences)
				//		{
				//			break;
				//		}
				//
				//		$intStartTime = strtotime($strtotime, $intStartTime);
				//		$intEndTime = strtotime($strtotime, $intEndTime);
				//
				//		// Stop if the upper boundary is reached (see #8445)
				//		if ($intStartTime === false || $intEndTime === false)
				//		{
				//			break;
				//		}
				//
				//		// Skip events outside the scope
				//		if ($intEndTime < $intStart || $intStartTime > $intEnd)
				//		{
				//			continue;
				//		}
				//
				//		$this->addEvent($objEvents, $intStartTime, $intEndTime, $intStart, $intEnd, $id);
				//	}
				//}
			//}
		}

		// Sort the array
		foreach (array_keys($this->arrEvents) as $key)
		{
			ksort($this->arrEvents[$key]);
		}

		// HOOK: modify the result set
		if (isset($GLOBALS['TL_HOOKS']['getAllEvents']) && \is_array($GLOBALS['TL_HOOKS']['getAllEvents']))
		{
			foreach ($GLOBALS['TL_HOOKS']['getAllEvents'] as $callback)
			{
				$this->import($callback[0]);
				$this->arrEvents = $this->{$callback[0]}->{$callback[1]}($this->arrEvents, $this->Template->products, $intStart, $intEnd, $this);
			}
		}

		return $this->arrEvents;
	}

	/**
	 * Add an event to the array of active events
	 *
	 * @param CalendarEventsModel $objEvents
	 * @param integer             $intStart
	 * @param integer             $intEnd
	 * @param integer             $intBegin
	 * @param integer             $intLimit
	 * @param integer             $intCalendar
	 */
	protected function addEvent($objEvent, $intStart, $intEnd, $intBegin, $intLimit, $intCalendar)
	{
		/** @var PageModel $objPage */
		global $objPage;

		// Backwards compatibility (4th argument was $strUrl)
		if (\func_num_args() > 6)
		{
			@trigger_error('Calling Events::addEvent() with 7 arguments has been deprecated and will no longer work in Contao 5.0. Do not pass $strUrl as 4th argument anymore.', E_USER_DEPRECATED);

			$intLimit = func_get_arg(5);
			$intCalendar = func_get_arg(6);
		}

		$intDate = $intStart;
		$intKey = date('Ymd', $intStart);
		$strDate = \Date::parse($objPage->dateFormat, $intStart);
		$strDay = $GLOBALS['TL_LANG']['DAYS'][date('w', $intStart)];
		$strMonth = $GLOBALS['TL_LANG']['MONTHS'][(date('n', $intStart)-1)];
		$span = \Calendar::calculateSpan($intStart, $intEnd);

		if ($span > 0)
		{
			$strDate = \Date::parse($objPage->dateFormat, $intStart) . $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . \Date::parse($objPage->dateFormat, $intEnd);
			$strDay = '';
		}

		$strTime = '';

		if ($objEvent->addTime)
		{
			if ($span > 0)
			{
				$strDate = \Date::parse($objPage->datimFormat, $intStart) . $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . \Date::parse($objPage->datimFormat, $intEnd);
			}
			elseif ($intStart == $intEnd)
			{
				$strTime = \Date::parse($objPage->timeFormat, $intStart);
			}
			else
			{
				$strTime = \Date::parse($objPage->timeFormat, $intStart) . $GLOBALS['TL_LANG']['MSC']['cal_timeSeparator'] . \Date::parse($objPage->timeFormat, $intEnd);
			}
		}

		$until = '';
		$recurring = '';

		// Recurring event
		//if ($objEvents->recurring)
		//{
		//	$arrRange = \StringUtil::deserialize($objEvents->repeatEach);
		//
		//	if (\is_array($arrRange) && isset($arrRange['unit']) && isset($arrRange['value']))
		//	{
		//		$strKey = 'cal_' . $arrRange['unit'];
		//		$recurring = sprintf($GLOBALS['TL_LANG']['MSC'][$strKey], $arrRange['value']);
		//
		//		if ($objEvents->recurrences > 0)
		//		{
		//			$until = sprintf($GLOBALS['TL_LANG']['MSC']['cal_until'], \Date::parse($objPage->dateFormat, $objEvents->repeatEnd));
		//		}
		//	}
		//}

		// Store raw data
		//$arrEvent = $objEvents->row();

		// Overwrite some settings
		$arrEvent['date'] = $strDate;
		$arrEvent['time'] = $strTime;
		$arrEvent['datetime'] = $objEvent->addTime ? date('Y-m-d\TH:i:sP', $intStart) : date('Y-m-d', $intStart);
		$arrEvent['day'] = $strDay;
		$arrEvent['month'] = $strMonth;
		//$arrEvent['parent'] = $intCalendar;
		//$arrEvent['calendar'] = $objEvent->getRelated('pid');
		$arrEvent['link'] = $objEvent->name;
		$arrEvent['target'] = '';
		$arrEvent['title'] = \StringUtil::specialchars($objEvent->name, true);

		$arrEvent['href'] = $objEvent->generateUrl($this->findJumpToPage($objEvent));
		//$arrEvent['class'] = ($objEvent->cssClass != '') ? ' ' . $objEvent->cssClass : '';
		//$arrEvent['recurring'] = $recurring;
		$arrEvent['until'] = $until;
		$arrEvent['begin'] = $intStart;
		$arrEvent['end'] = $intEnd;
		$arrEvent['details'] = '';
		$arrEvent['hasDetails'] = false;
		$arrEvent['hasTeaser'] = false;

		// Override the link target
		//if ($objEvent->source == 'external' && $objEvent->target)
		//{
		//	$arrEvent['target'] = ' target="_blank"';
		//}
		//
		// Clean the RTE output
		if ($objEvent->teaser)
		{
			$arrEvent['hasTeaser'] = true;
			$arrEvent['teaser'] = \StringUtil::toHtml5($objEvent->teaser);
			$arrEvent['teaser'] = \StringUtil::encodeEmail($arrEvent['teaser']);
		}

		// Display the "read more" button for external/article links
		//if ($objEvent->source != 'default')
		//{
		//	$arrEvent['details'] = true;
		//	$arrEvent['hasDetails'] = true;
		//}

		// Compile the event text
		//else
		//{
		//	$id = $objEvent->id;
		//
		//	$arrEvent['details'] = function () use ($id)
		//	{
		//		$strDetails = '';
		//		$objElement = \ContentModel::findPublishedByPidAndTable($id, 'tl_calendar_events');
		//
		//		if ($objElement !== null)
		//		{
		//			while ($objElement->next())
		//			{
		//				$strDetails .= $this->getContentElement($objElement->current());
		//			}
		//		}
		//
		//		return $strDetails;
		//	};
		//
		//	$arrEvent['hasDetails'] = function () use ($id)
		//	{
		//		return \ContentModel::countPublishedByPidAndTable($id, 'tl_calendar_events') > 0;
		//	};
		//}

		// Get todays start and end timestamp
		if ($this->intTodayBegin === null)
		{
			$this->intTodayBegin = strtotime('00:00:00');
		}
		if ($this->intTodayEnd === null)
		{
			$this->intTodayEnd = strtotime('23:59:59');
		}

		// Mark past and upcoming events (see #3692)
		if ($intEnd < $this->intTodayBegin)
		{
			$arrEvent['class'] .= ' bygone';
		}
		elseif ($intStart > $this->intTodayEnd)
		{
			$arrEvent['class'] .= ' upcoming';
		}
		else
		{
			$arrEvent['class'] .= ' current';
		}

		$this->arrEvents[$intKey][$intStart][] = $arrEvent;

		// Multi-day event
		for ($i=1; $i<=$span; $i++)
		{
			// Only show first occurrence
			if ($this->cal_noSpan)
			{
				break;
			}

			$intDate = strtotime('+1 day', $intDate);

			if ($intDate > $intLimit)
			{
				break;
			}

			$this->arrEvents[date('Ymd', $intDate)][$intDate][] = $arrEvent;
		}
	}

	/**
	 * Return empty string to turn cache off.
	 *
	 * @return string A 32 char cache key (e.g. MD5)
	 */
	protected function getCacheKey()
	{
		return '';
	}
}
