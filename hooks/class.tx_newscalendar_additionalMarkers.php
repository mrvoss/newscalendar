<?php

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));

/**
 * Use HOOK for additional Markers in tt_news
 *
 * @Startdate of event
 * @Enddate of event
 *
 */
class tx_newscalendar_additionalMarkers {

	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$lang = t3lib_div::makeInstance('language');
		$lang->init($GLOBALS['TSFE']->lang);
		$dateToDate = $lang->sL('LLL:EXT:newscalendar/pi1/locallang.xml:dateToDate');
		
		// Add Marker start date, but only if necessary / not empty
		if ($row['tx_newscalendar_calendardate'] == 0) {
			$markerArray['###NEWS_STARTDATE###'] = '';
		}
		else {
			$markerArray['###NEWS_STARTDATE###'] = $pObj->cObj->stdWrap($row['tx_newscalendar_calendardate'], $lConf['date_stdWrap.']);
		}
		// Add Marker end date, but only if necessary / not empty
		if ($row['tx_newscalendar_calendardate_end'] == 0) {
			$markerArray['###NEWS_ENDDATE###'] = '';
		}
		else {
			$markerArray['###NEWS_ENDDATE###'] = $pObj->cObj->stdWrap($row['tx_newscalendar_calendardate_end'], $lConf['date_stdWrap.']);
		}

		// Add marker startendate
		if($row['tx_newscalendar_calendardate'] != 0) {
			$markerArray['###NEWS_STARTENDATE###'] = $pObj->cObj->stdWrap($row['tx_newscalendar_calendardate'], $lConf['date_stdWrap.']);
			if ($row['tx_newscalendar_calendardate'] != $row['tx_newscalendar_calendardate_end'] && $row['tx_newscalendar_calendardate_end'] != 0) {
				$markerArray['###NEWS_STARTENDATE###'] .= " " . $dateToDate . " " . $pObj->cObj->stdWrap($row['tx_newscalendar_calendardate_end'], $lConf['date_stdWrap.']);
			}
		}
		else
		{
			$markerArray['###NEWS_STARTENDATE###'] = $pObj->cObj->stdWrap($row['datetime'], $lConf['date_stdWrap.']);
		}
		return $markerArray;
	}
}
?>