<?php

//gregory goidin - rvvn : start : load the lang class
require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
//gregory goidin - rvvn : stop !

/**
 * Use HOOK for additional Markers in tt_news
 *
 * @Startdate of event
 * @Enddate of event
 *
 */

class tx_newscalendar_additionalMarkers {

	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		//gregory goidin - rvvn : start : load the locallang value of dateToDate
		$lang = t3lib_div::makeInstance('language');
		$lang->init($GLOBALS['TSFE']->lang);
		$dateToDate = $lang->sL('LLL:EXT:newscalendar/pi1/locallang.xml:dateToDate');
		//gregory goidin - rvvn : stop !
		
		// RICC: use the parent cObject for this!
		//$this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj. so we can use stdWrap
		
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

		//gregory goidin - rvvn : start : Add marker startendate
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
		//gregory goidin - rvvn : stop !
		return $markerArray;
	}
}
?>