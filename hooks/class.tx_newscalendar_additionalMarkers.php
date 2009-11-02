<?php

/** Use HOOK for additional Markers in tt_news
 *
 *  @Startdate of event
 *  @Enddate of event
 *  
 *  (c) 2009 Michael Hitzler, paravista media  
 *
 */   

class tx_newscalendar_additionalMarkers {    

	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$obj) {

        $this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj. so we can use stdWrap
        
        // Add Marker start date, but only if necessary / not empty
        if ($row['tx_newscalendar_calendardate'] == 0) {
			$markerArray['###NEWS_STARTDATE###'] = '';
		} else {
			$markerArray['###NEWS_STARTDATE###'] = $this->local_cObj->stdWrap( $row['tx_newscalendar_calendardate'], $lConf['date_stdWrap.'] );
		}


        // Add Marker end date, but only if necessary / not empty
        if ($row['tx_newscalendar_calendardate_end'] == 0) {
            $markerArray['###NEWS_ENDDATE###'] = '';
        } else {
            $markerArray['###NEWS_ENDDATE###'] = $this->local_cObj->stdWrap($row['tx_newscalendar_calendardate_end'], $lConf['date_stdWrap.']);
        }
        
        return $markerArray;

    }
}
?>