<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Philip Almeida <philip.almeida@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

// RICC 2013: not needed anymore since TYPO3-Autoloader
//require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin 'News Calendar' for the 'newscalendar' extension.
 *
 * @author	Philip Almeida <philip.almeida@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_newscalendar
 */
class tx_newscalendar_pi1 extends tslib_pibase {


    var $prefixId       = 'tx_ttnews';		// Same as class name
    var $scriptRelPath	= 'pi1/class.tx_newscalendar_pi1.php';	// Path to this script relative to the extension dir.
    var $extKey		= 'newscalendar';	// The extension key.
    var $pi_checkCHash	= true;
    var $resultList;
    var $resultListCount;
    var $globalRes;


    // RICC begin: add uploads folder of tt_news as gVar; can probably better retrieved later on from some TS settings?
    var $uploadFolder = 'uploads/pics/';
    var $jsContextMenu;
    // RICC end


    /**
     * The main method of the PlugIn
     *
     * @param	string		$content: The PlugIn content
     * @param	array		$conf: The PlugIn configuration
     * @return	The content that is displayed on the website
     */

    function main($main,$conf) {

        global $LANG;
        $this->conf = $conf;
        $this->pi_initPIflexForm();	// Init FlexForm configuration for plugin
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        $this->pi_USER_INT_obj=0;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

        // Added &no_cache=1 to the link, now the site can be cachable and only if a user click on nextmonth/previousmonth
        // the site will not be cached and the month navigator will work fine
        // "Markus Waskowski" growing-media.de

        // Possible to deactivate &no_cache=1 (http://bugs.typo3.org/view.php?id=8810)
        $this->cacheAdd = 0;
        if ( $this->conf['calendar.']['addNoCache2Navigation'] ) {
            $this->cacheAdd = 1;
        }

        /*
		* Retrieve day vars
        */
        $this->time             = time();
        $this->thisDay		= date('j',time());
        $this->thisYear		= date('Y',time());
        $this->thisMonth	= date('n',time());

        /*
		* Define parser function "hmtlentities" or "htmlspecialchars"
        */
        $this->parserFunction	= ($this->conf['special.']['parserFunction']?$this->conf['special.']['parserFunction']:'htmlspecialchars');

        /*
		* Retrieve conf variables for css definitions
        */
        // RICC begin -> Do it the more easy way without ifs via std t3 api funcs.
        // You have just to cut the PATH_site for getting the real relative urls.
        // Now you can overwrite it easily within your own TS setup
        // (I experienced problems with that on my systems) and it
        // is better for strange installations (realurl; subfolder installation of t3)
        // 2008-05-30: Added possibility to configure js-library for compressed versions etc.
        $this->cssCalendar	= str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['cssCalendar']));
        $this->cssContextMenu	= str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['cssContextMenu']));
        $this->jsContextMenu	= str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['jsContextMenu']));
        $this->jsJQuery		= str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['jsJQuery']));
        $this->jsJQueryTooltip	= str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['jsJQueryTooltip']));
        $this->jsDateChanger	= str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['jsDateChanger']));

        $this->jsNewscalendar	= str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['jsNewscalendar']));

        // Include IE canvas API
        if ( $this->conf['calendar.']['loadJGoogleCanvasAPI'] ) {
            $jGoogleCanvas = str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['file.']['jsIEGoogleCanvasAPI']));
            $jGoogleCanvas = '<!--[if IE]><script type="text/javascript" src="' . $jGoogleCanvas . '"></script><![endif]-->' . "\n";
        }
        // Include jQuery API
        if ( $this->conf['calendar.']['loadJQuery'] ) {
            $jsJQuery = '<script type="text/javascript" src="'.$this->jsJQuery.'"></script>' . "\n";
        }
        // Include tooltip API
        if ( $this->conf['calendar.']['loadJQueryTooltip'] ) {
            $jsJQueryTooltip = '<script type="text/javascript" src="'.$this->jsJQueryTooltip.'"></script>' . "\n";
        }

        /**
         * IE8 compat
         */
        if ( $this->conf['render.']['ie7compat'] ) {
            $ie7compat = "<!-- EXT:newscalendar: IE8 Tip Compatibility: START --> " . "\n" .
                                        '<meta http-equiv="X-UA-Compatible" content="IE=7" />' . "\n" .
                                        "<!-- EXT:newscalendar: IE8 Tip Compatibility: END --> " . "\n\n";
        }

        $GLOBALS['TSFE']->additionalHeaderData['tx_newscalendar_inc']
                =   "\n" . 
                $ie7compat . 
                "<!-- EXT:newscalendar: Javascript and CSS include files : START --> " . "\n" .
                '<link href="' . $this->cssCalendar	 . '" rel="stylesheet" type="text/css" />' . "\n" .
                '<link href="' . $this->cssContextMenu . '" rel="stylesheet" type="text/css" />' . "\n" .
                $jGoogleCanvas . $jsJQuery . $jsJQueryTooltip .
                '<script type="text/javascript" src="' . $this->jsNewscalendar . '"></script>' . "\n" .
                '<!-- EXT:newscalendar: Javascript and CSS include files : END --> ' . "\n";

        /*
		* Set template file for list view
        */
        // RICC begin -> Do it the most easiest way via t3 api funcs.
        // Now you can overwrite it easily within your own TS setup -> see above
        $this->listViewTemplate = $this->cObj->fileResource($this->conf['file.']['listViewTemplate']);
        // RICC end

        $this->calendarViewTemplate = $this->cObj->fileResource($this->conf['file.']['calendarViewTemplate']);


        // Display Type ( calendar, listview, listinterval, nextevents )
        $this->displayType = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'displayType', 'sDEF');
        if($this->conf['render.']['displayType'])
            $this->displayType = $this->conf['render.']['displayType'];

        // Show link to list
        // When active there will be a link on the calendar month and bottom
        // to the configured list type..
        $this->monthLinkDisplay = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'monthLinkDisplay', 'sDEF');
        if($this->conf['render.']['monthLinkDisplay'])
            $this->monthLinkDisplay = $this->conf['render.']['monthLinkDisplay'];


        // Connect all navigators to tt_news list display.
        // Next & Previous and also links to current month events
        // It needs a tt_news list plugin.

        $this->monthNavigationViaNews = $this->conf['render.']['monthNavigationViaNews'];



        // Uid of list page
        $this->listPage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listPage', 'sDEF');
        if($this->conf['render.']['listPage'])
            $this->listPage = $this->conf['render.']['listPage'];
        // News item single view
        $this->singleView = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'destinationPage', 'sDEF');
        if($this->conf['render.']['singleView'])
            $this->singleView = $this->conf['render.']['singleView'];
        // Single news item backpage
        $this->backPage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'backPage', 'sDEF');
        if($this->conf['render.']['backPage'])
            $this->backPage = $this->conf['render.']['backPage'];
        // Starting point for news records
        $this->startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidList', 'sDEF');
        if($this->conf['render.']['startingPoint'])
            $this->startingPoint = $this->conf['render.']['startingPoint'];
        // Recursion for starting point
        $this->recursion = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursion', 'sDEF');
        if($this->conf['render.']['recursion'])
            $this->recursion = $this->conf['render.']['recursion'];
        // Day length
        $this->dayNameLength = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dayNameLength', 'sDEF');
        if($this->conf['render.']['dayNameLength'])
            $this->dayNameLength = $this->conf['render.']['dayNameLength'];
        // Context menu link type
        $this->contextMenuLink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'contextMenuLink', 'sDEF');
        if($this->conf['render.']['contextMenuLink'])
            $this->contextMenuLink = $this->conf['render.']['contextMenuLink'];

        // Category List
        $this->categorySelection = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'categorySelection', 'sDEF');
        if($this->conf['render.']['categorySelection'])
            /**
             * http://forge.typo3.org/issues/show/7266
             * This will add stdWrap capabilies to the parameter and enable Features like categorySelection.data = GPvar : tx_ttnews|cat
             */
            $this->categorySelection = $this->cObj->stdWrap($this->conf['render.']['categorySelection'], $this->conf['render.']['categorySelection.']);

        $this->useSubCategories = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'useSubCategories', 'sDEF');
        if($this->conf['render.']['useSubCategories'])
            $this->useSubCategories = $this->conf['render.']['useSubCategories'];

        /*
         * Get the PID from which to make the menu.
        */

        if ( $this->startingPoint ) {
            // If a page is set as reference in the 'Startingpoint' field, use that
            $this->menuPid = $this->startingPoint;
        } else {
            // Otherwise use the page's id-number from TSFE
            $this->menuPid = $GLOBALS['TSFE']->id;
        }

        // Define Calendar rendering dates
        $this->calendarYear = intval($this->piVars['calendarYear']) ? intval($this->piVars['calendarYear']):$this->thisYear;
        $this->calendarMonth = intval($this->piVars['calendarMonth']) ? intval($this->piVars['calendarMonth']):$this->thisMonth;

        // tt_news default navigation dates
        // Get Year and Month from tt_news,
        // it assumes that they are present on url parameters.
        if ( $this->monthNavigationViaNews ) {
            $this->calendarYear = intval( $this->piVars['year']) ? intval($this->piVars['year']):$this->thisYear;
            $this->calendarMonth = intval( $this->piVars['month']) ? intval($this->piVars['month']):$this->thisMonth;
        }

        $this->calendarMonth = str_pad( $this->calendarMonth, 2, "0", STR_PAD_LEFT);

        /**
         *  Lang configuration
         */
        // CHANGED BY RICC
        //setlocale(LC_ALL, $GLOBALS['TSFE']->tmpl->setup['config.']['locale_all']);
        setlocale(LC_ALL, $GLOBALS['TSFE']->tmpl->setup['config.']['locale_all'].".".$GLOBALS['TSFE']->tmpl->setup['config.']['renderCharset']);
        // END CHANGED BY RICC


        // gregory goidin - rvvn
        // The render method is set in the main to prevent using useless code.

        $this->buildCalendarArray( );

        /**
         * 1- Calendar
         * 2- List
         * 3- List Interval
         * 4- NextEvents
         */

        switch( $this->displayType ) {

            case 1:
                return $this->calendarEngine();

            case 3:
                return $this->pi_wrapInBaseClass($this->listView());

            default:
                return $this->pi_wrapInBaseClass($this->listViewNormal());


        }

    }

    // +---------------------------------------------------------
    // | Calendar array
    // | All query building is done here.
    // +---------------------------------------------------------

    function buildCalendarArray() {

        // Define the list of pages to search (999 depth level).
        $this->search_list = $this->pi_getPidList( $this->menuPid,$this->recursion );

        // Display only records that are active.
        $this->splitQuery = 'AND tx_newscalendar_state = 1';

        // If showAllRecors is active, display all records.
        if( $this->conf['show.']['allRecords'] )
            $this->splitQuery = '';


        // gregory goidin - rvvn
        // Calculate the first and last date of the month.


        $currentTime = $this->time;
        $firstDate = $calendarStartMonth = strtotime( $this->calendarYear . '-' . $this->calendarMonth . '-01' );
        $lastDate = $calendarEndMonth = mktime( 23, 59, 59, intval( $this->calendarMonth ), date( 't', $calendarStartMonth ), intval( $this->calendarYear ) );

        switch ( $this->displayType ) {
            case 1: // CALENDAR
                if ( $this->conf['calendar.']['hideIfInPast'] == 1
                        && $currentTime >= $calendarStartMonth
                        && $currentTime <= $calendarEndMonth ) {
                    $firstDate = $currentTime;
                }
                else if( $this->conf['calendar.']['hideIfInPast'] == 1
                        && $currentTime > $calendarEndMonth ) {
                    $firstDate = 1;
                    $lastDate = 1;
                }
                break;
            case 2: // LISTVIEW
                if ( $this->conf['listView.']['hideIfInPast'] == 1
                        && $currentTime >= $calendarStartMonth
                        && $currentTime <= $calendarEndMonth ) {
                    $firstDate = $currentTime;
                }
                else if ( $this->conf['listView.']['hideIfInPast'] == 1 && $currentTime > $calendarEndMonth ) {
                    $firstDate = 1;
                    $lastDate = 1;
                }
                break;
            case 3: // LISTINTERVAL
                if ( $this->conf['listInterval.']['hideIfInPast'] == 1
                        && $currentTime >= $calendarStartMonth
                        && $currentTime <= $calendarEndMonth) {
                    $firstDate = $currentTime;
                }
                else if ( $this->conf['listInterval.']['hideIfInPast'] == 1
                        && $currentTime > $calendarEndMonth ) {
                    $firstDate = 1;
                    $lastDate = 1;
                }
                break;
            case 4: // NEXTEVENTS
                if ( $this->conf['nextEvents.']['relativeToCalendar'] == 1
                        && $currentTime <= $calendarStartMonth ) {
                    $firstDate = $calendarStartMonth;
                }
                else {
                    $firstDate = $currentTime;
                }
                break;
        }


        // Language query setup
        if ($this->sys_language_mode == 'strict' && $GLOBALS['TSFE']->sys_language_content) {
            // Just news in the same language
            $langClause = 'tt_news.sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_content;
        } else {
            // sys_language_mode != 'strict': If a certain language is requested, select only news-records in the default language. The translated articles (if they exist) will be overlayed later in the list or single function.
            $langClause = 'tt_news.sys_language_uid IN (0,-1)';
        }

        // gregory goidin - rvvn
        // New way of creating the sql query ( new display ).
        if ( $this->displayType == 4 ) {
            $queryYearMonth = 'AND ( (tx_newscalendar_calendardate=0 AND datetime >= '.$firstDate.' ) '.
                    'OR ( tx_newscalendar_calendardate_end=0 AND tx_newscalendar_calendardate >= '.$firstDate.' ) '.
                    'OR ( tx_newscalendar_calendardate_end >= '.$firstDate.' ) )';
        }
        else {
            $queryYearMonth = 'AND ( (tx_newscalendar_calendardate=0 AND datetime >= '.$firstDate.' AND datetime <= '.$lastDate.' ) '.
                    'OR ( tx_newscalendar_calendardate_end=0 AND tx_newscalendar_calendardate >= '.$firstDate.' AND tx_newscalendar_calendardate <= '.$lastDate.' ) '.
                    'OR ( tx_newscalendar_calendardate <= '.$lastDate.' AND tx_newscalendar_calendardate_end >= '.$firstDate.' ) )';
        }


        $this->where =	$langClause         . ' ' .
                $queryYearMonth             . ' ' .
                $this->splitQuery           . ' ' .
                $this->cObj->enableFields( 'tt_news' );




        // gregory goidin - rvvn
        // add the limit parameter to configure the browsebox if needed in the sql query

        if ( $this->displayType == 2
                && intval( $this->conf['pageBrowser.']['show'] ) ==  1
                && intval( $this->conf['pageBrowser.']['limit'] ) >= 1
                && intval( $this->conf['pageBrowser.']['maxPages'] ) >= 1 ) {
            $limit = "";
            $currentPage = 0;
            if ( intval( $this->piVars[$this->conf['pageBrowser.']['pointer']] ) > 0 ) {
                $currentPage = intval( $this->piVars[$this->conf['pageBrowser.']['pointer']] );
            }
            $limit = (intval($this->conf['pageBrowser.']['limit'])*($currentPage)).",".intval($this->conf['pageBrowser.']['limit']);
        }
        else if ( $this->displayType == 4
                && intval($this->conf['pageBrowser.']['show']) == 1
                && intval($this->conf['pageBrowser.']['limit']) >= 1
                && intval($this->conf['pageBrowser.']['maxPages']) >= 1 ) {
            $limit = "";
            $currentPage = 0;
            if ( intval($this->piVars[$this->conf['pageBrowser.']['pointer']]) > 0 ) {
                $currentPage = intval( $this->piVars[$this->conf['pageBrowser.']['pointer']] );
            }
            $limitMax = intval($this->conf['pageBrowser.']['limit']);
            if ( (intval($this->conf['pageBrowser.']['limit'])*($currentPage+1)) > intval($this->conf['nextEvents.']['maxItems'])) {
                $limitMax = intval($this->conf['nextEvents.']['maxItems'])-(intval($this->conf['pageBrowser.']['limit'])*($currentPage));
            }
            $limit = (intval($this->conf['pageBrowser.']['limit'])*($currentPage)).",".$limitMax;
        }
        else if ($this->displayType == 4 && intval($this->conf['pageBrowser.']['show'])==0) {
            $limit = "0,".intval($this->conf['nextEvents.']['maxItems']);
        }



        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                'tt_news.uid,
                tt_news.pid,
                tt_news.sys_language_uid,
                tt_news.title,
                tt_news.datetime,
                tt_news.tx_newscalendar_calendardate_end,
                tt_news.tx_newscalendar_calendardate,
                tt_news.short,
                tt_news.image,
                tt_news.imagecaption,
                tt_news.imagealttext,
                tt_news.imagetitletext,
                tt_news.bodytext,
                tt_news.page,
                tt_news.type,
                tt_news.ext_url',
                'tt_news',
                $this->where . ' AND tt_news.pid in (' . $this->search_list . ')',
                '',
                'tx_newscalendar_calendardate, tt_news.datetime ASC',
                $limit
        );

        $this->globalRes = $res;

        //****************************************************************//
        //gregory goidin - rvvn : start : We need the total count to initialize the pageBrowser

        /**
         * If the type of view is able to have  a page browser we need to count the events again
         * so we have the total record count for the paginator.
         * We bypass the Calendar View by default.
         */
        if ( $this->displayType != 1 ) {

            $resCount = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    'count(*)',
                    'tt_news',
                    $this->where . ' AND tt_news.pid in (' . $this->search_list . ')',
                    '',
                    'tx_newscalendar_calendardate, tt_news.datetime ASC',
                    ""
            );

            $rowCount = $GLOBALS['TYPO3_DB']->sql_fetch_row($resCount);

            if (    $this->displayType == 4
                    && $rowCount[0] > intval( $this->conf['nextEvents.']['maxItems'] ) ) {
                    $rowCount[0] = intval( $this->conf['nextEvents.']['maxItems'] );
            }

            $this->globalResCount = $rowCount[0];

        } else {

            // For calendar view we do not use limits or paginator browser
            $this->globalResCount = $GLOBALS[ 'TYPO3_DB' ]->sql_num_rows( $this->globalRes );

        }

        //gregory goidin - rvvn : stop !
        //****************************************************************//

        // Prevent interval item rendering on normal and next events list views.
        // Only this will be prepared to render selected categories.

        if ( $this->displayType == 1 || $this->displayType == 3 ) {

            // Fill array with result set.
            // CHANGED BY RICC FROM '1' to '0'
            $arrayCounter = 0;
            if ( $this->categorySelection && $this->useSubCategories ) {
                $this->categorySelection .= ',' . tx_ttnews_div::getSubCategories( $this->categorySelection );
            }

            while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

                /**
                 * START: News category in list if applicable
                 */

                if ( $this->categorySelection ) {

                    $exists = false;
                    $categoryArray = explode( ",", $this->categorySelection );

                    foreach ( $categoryArray as $selectedCatId ) {
                        $exists = array_key_exists( $selectedCatId,  tx_ttnews::getCategories( $row['uid'] ) );
                        if ( $exists ) break;
                    }

                    if ( ! $exists ) continue;

                }

                /**
                 * STOP: News category in list if applicable
                 */

                // News item default types
                $resultList [$arrayCounter]['type'] = $row['type'];
                $resultList [$arrayCounter]['page'] = $row['page'];
                $resultList [$arrayCounter]['ext_url'] = $row['ext_url'];

                $resultList [$arrayCounter]['image'] = $row['image'];
                $resultList [$arrayCounter]['short'] = $row['short'];

                $auxiliar_data = $resultList;

                // get the translated record if the content language is not the default language
                if ($GLOBALS['TSFE']->sys_language_content) {
                    // $OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
                    $OLmode = $GLOBALS['TSFE']->tmpl->setup['config.']['sys_language_overlay'];
                    $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
                }

                // Choose startdate, preference for tx_newscalendardate over datetime.
                $finalStartDate = ( $row['tx_newscalendar_calendardate'] ? $row['tx_newscalendar_calendardate'] : $row['datetime'] );

                if ( $this->calendarYear == date( 'Y', $finalStartDate ) && $this->calendarMonth == date( 'n', $finalStartDate ) ) {
                    $resultList [$arrayCounter]['uid']	= $row['uid'];
                    $resultList [$arrayCounter]['title']	= $row['title'];
                    $resultList [$arrayCounter]['monthday']	= date('j', $finalStartDate );
                    $resultList [$arrayCounter]['enddate']	= $row['tx_newscalendar_calendardate_end'];
                    $resultList [$arrayCounter]['datetime']	= $finalStartDate;
                    $arrayCounter ++;
                }

                if ( $row['tx_newscalendar_calendardate_end'] > 0 ) {
                    $startDate	= date( 'Y-m-d', $finalStartDate );
                    $endDate	= date( 'Y-m-d', $row['tx_newscalendar_calendardate_end'] );
                    $daysBetween	= $this->GetDays( $startDate, $endDate, $row['uid'] );
                    while( $date = each( $daysBetween ) ) {
                        if ( $startDate != $date[1] ) {
                            $timeDate = strtotime( $date[1] . date(' H:i', $finalStartDate ) );
                            if ( $this->calendarYear == date( 'Y', $timeDate ) && $this->calendarMonth == date( 'n', $timeDate ) ) {

                                $resultList [$arrayCounter]['uid']	= $row['uid'];
                                $resultList [$arrayCounter]['title']	= $row['title'];
                                $resultList [$arrayCounter]['monthday']	= date('j',$timeDate);
                                $resultList [$arrayCounter]['enddate']	= 0;
                                $resultList [$arrayCounter]['datetime']	= $timeDate;

                                $resultList [$arrayCounter]['type']	= $row['type'];
                                $resultList [$arrayCounter]['page']	= $row['page'];
                                $resultList [$arrayCounter]['ext_url']	= $row['ext_url'];

                                $resultList [$arrayCounter]['image']	= $row['image'];
                                $resultList [$arrayCounter]['short']	= $row['short'];


                                /**
                                 * Remove items when in a interval and "hideIfInPast" is on
                                 * $this->conf['calendar.']['hideIfInPast']
                                 * Thanks to Marvin Stobberingh for reporting.
                                 */

                                if ( $this->conf['calendar.']['hideIfInPast']  ) {

                                    if ( $resultList [$arrayCounter]['datetime'] < time() ) {

                                        unset( $resultList [$arrayCounter - 1 ] );

                                    } else {  $arrayCounter ++; }

                                } else { $arrayCounter ++; }

                            }

                        }
                    }
                }
            }

            # sort alphabetically by name
            if(is_array($resultList)) {
                usort($resultList, array($this,'compare_datetime'));
            }

            $this->resultListCount = count($resultList);
            $this->resultList = $resultList;
        }
        return;
    }

    /**
     * The calendarQuery method of the PlugIn
     *
     * @param	int		$year: The year for the calendar
     * @param	int		$month: The month for the calendar
     * @return	The content for the calendar that is displayed on the website
     */
    function calendarEngine() {
        global $TSFE;

        $this->previousNews = intval($this->piVars['tt_news']);

        // Thanks to Maxim Levicky
        if ( $this->conf['calendar.']['dateChanger'] ) {
            // TODO: Move to header section
            $contextScript .= '<script type="text/javascript" src="'.$this->jsDateChanger.'"></script>' . "\n";
        }

        // Javascript argument list
        $tipArgumentList =
                $this->conf['tip.']['width'] . ", " .
                "'" . $this->conf['tip.']['backgroundColor'] . "', " .
                "'" . $this->conf['tip.']['borderColor'] . "', " .
                $this->conf['tip.']['borderWidth'] . ", " .
                $this->conf['tip.']['radius'] . ", " .
                $this->conf['tip.']['padding'] . ", " .
                $this->conf['tip.']['spikeLength'] . ", " .
                $this->conf['tip.']['spikeGirth'] . ", " .
                $this->conf['tip.']['shadow'] . ", " .
                $this->conf['tip.']['shadowBlur'] . ", " .
                $this->conf['tip.']['shadowOffsetX'] . ", " .
                $this->conf['tip.']['shadowOffsetY'] . ", " .
                $this->conf['tip.']['positions'] . ", " .
                $this->conf['tip.']['fadeSpeed'];

        $contextScript .= "\n";
        $contextScript .= "\t" ."<!-- Newscalendar: Activate tooltip --> " . "\n\n";
        $contextScript .= "\t" ."<script language=\"javascript\">" . "\n";

        // Bug #6114
        if ( $this->conf['calendar.']['jQueryNoConflict'] )
            $contextScript .= "\t" ."jQuery.noConflict(); " . "\n";

        $contextScript .= "\t" ."jQuery( document ).ready( function() {" . "\n";

        $contextScript .= "\t\t" ."newscalendar.tipSetup(" . $tipArgumentList . ");" . "\n";

        $renderedDays = array();

        if( is_array($this->resultList ) ) {
            reset( $this->resultList );
            while ( list( $key, $val ) = each( $this->resultList ) ) {
                $dateTime = $val['monthday'];

                if ( ! is_array($renderedDays[ $val['monthday'] ] ) ) {
                    // Create tooltip
                    $contextScript .= "\t\t" . "newscalendar.processToolTip( " . $dateTime . " )" . "\n";
                }

                $renderedDays[ $val['monthday'] ][] = $val;

                if ($this->thisDay==$dateTime && $this->thisYear==$this->calendarYear && $this->thisMonth==$this->calendarMonth) {

                    $daysLinkArray[$dateTime] = array('idMenu'.$dateTime,'linked_today');

                } else {

                    $daysLinkArray[$dateTime] = array('idMenu'.$dateTime,'linked_day');


                }
            }
        }

        // Process tips
        $contextScript_tips = $this->buildContextMenu( $renderedDays );

        $contextScript .= "\t" . "});" . "\n";
        $contextScript .= "\t" . "</script>" . "\n";

        // Join Tips
        $contextScript = $contextScript_tips . $contextScript;

        /*
	* Set tt_news to the previous id so it sticks on navigation.
        */
				// by RICC 2013, #48025
        //$this->piVars['tt_news'] = $this->previousNews;
				if ($this->previousNews > 0) {
					$this->piVars['tt_news'] = $this->previousNews;
				} else {
					$this->piVars['tt_news'] = null;
				}

        /*
		* Reset pointer.
        */
        $this->previousNews = intval($this->piVars['pointer']);
        $this->piVars['pointer']=null;

        /*
		* Set date values for previous link.
        */
        $this->piVars['calendarYear'] =($this->calendarMonth==1?$this->calendarYear-1:$this->calendarYear);
        $this->piVars['calendarMonth'] = ($this->calendarMonth==1?12:$this->calendarMonth-1);

        /*
		* Build previous link
        */
        $overrulePIvars = array();

        if ( $this->monthNavigationViaNews )
            $overrulePIvars = array('month'=> $this->piVars['calendarMonth'],
                    'year' => $this->piVars['calendarYear'],
                    'calendarMonth' => null,
                    'calendarYear' => null);

        $this->linkPrev = $this->pi_linkTP_keepPIvars_url( $overrulePIvars, $cache=$this->cacheAdd,$clearAnyway=0);

        /*
		* Set date values for next link.
        */
        $this->piVars['calendarYear'] =($this->calendarMonth==12?$this->calendarYear+1:$this->calendarYear);
        $this->piVars['calendarMonth'] = ($this->calendarMonth==12?1:$this->calendarMonth+1);

        /*
		* Build next link
        */
        $overrulePIvars = array();

        if ( $this->monthNavigationViaNews )
            $overrulePIvars = array('month'=> $this->piVars['calendarMonth'],
                    'year' => $this->piVars['calendarYear'],
                    'calendarMonth' => null,
                    'calendarYear' => null);

        $this->linkNext = $this->pi_linkTP_keepPIvars_url( $overrulePIvars, $cache=$this->cacheAdd,$clearAnyway=0);

        /*
		* Reset date values.
        */
        $this->piVars['calendarYear'] = $this->calendarYear;
        $this->piVars['calendarMonth'] = $this->calendarMonth;

        if ( $this->monthNavigationViaNews ) {
            unset( $this->piVars['calendarMonth']);
            unset( $this->piVars['calendarYear']);
        }

        /*
		* If $this->listPage is set then we will build calendar with link to month view.
        */

        if ($this->listPage) {

            $this->piVars['startingPoint'] = $this->startingPoint;
            $this->piVars['recursion'] = $this->recursion;

            $this->linkList = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array(),$cache=0,$clearAnyway=0, $this->listPage);
        }

        /*
		* Reset pointer.
        */
        $this->piVars['pointer']=$this->previousNews;

        $this->np = array('&laquo;' => $this->linkPrev,'&raquo;' => $this->linkNext);


        $calendar = $this->generate_calendar($this->calendarYear, $this->calendarMonth, $daysLinkArray, $this->dayNameLength,($this->listPage?$this->linkList:NULL),$this->conf['calendar.']['startWeekDay'],$this->np);

        // gregory goidin - rvvn
        // now, the render method is set in the main to prevent using useless code

        return $this->pi_wrapInBaseClass($calendar.$contextScript);

    }

    /**
     * The buildContextMenu method of the PlugIn
     *
     * @param	int		$day: The day for news
     * @return	The javascript to build menus
     */
    function buildContextMenu( $records ) {

        $checkDay = false;

        while ( list( $key, $dateItem ) = each( $records ) ) {

            if ( $checkDay != $key ) {

                $checkDay = $key;

                $displayDate	= mktime(0, 0, 0, $this->calendarMonth, $checkDay, $this->calendarYear);
                $displayDate	= $this->convertSpecialCharacters(strftime($this->conf['calendar.']['strftime.']['contextHeader'], $displayDate));

                // Build item tooltip

                $currentMenu = "menu" . $checkDay;
                $contextMenuScript .= "\n" . "\t". "<!-- Rendering newscalendar item " . $currentMenu . " --> \n\n";
                $contextMenuScript .= "\t"		. "<div class='newscalendarTooltip' id='toolTipIdMenu" . $checkDay . "'>" . "\n";
                $contextMenuScript .= "\t\t"	. "<div class='newscalendarTooltipHeader'>" . "\n";
                $contextMenuScript .= "\t\t\t"	. $displayDate . "\n";
                $contextMenuScript .= "\t\t"	.  "</div>" . "\n";
                $contextMenuScript .= "\t\t"	.  "<div class='newscalendarTooltipItemContainer'>" . "\n";

                while ( list( $key, $val ) = each( $dateItem ) ) {

                    $this->internal['currentRow'] = $val;
                    $contextMenuScript .= $this->makeListItemNormal( 'calendar' );

                }

                $contextMenuScript .= "\t\t" . '</div>' . "\n";
                $contextMenuScript .= "\t" . '</div>' . "\n";

            }

        }



        return $contextMenuScript;
    }


    /***********************************************************************
	 *
         * method: generate_calendar
         *
         * PHP Calendar (version 2.3), written by Keith Devens
	 * http://keithdevens.com/software/php_calendar
	 * see example at http://keithdevens.com/weblog
	 * License: http://keithdevens.com/software/license
         *
         *
	 ***********************************************************************/
    function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()) {
        /*
		* Add language add-ons for calendar
        */
        global $LANG;

        // TT_NEWS default navigation
        if ( $this->monthNavigationViaNews )
            $month_href= $this->pi_linkTP_keepPIvars_url( array( 'cat' => null, 'day' => null, 'year' => $year, 'month' => $month) );


        // Set timezone manually
        if ( $this->conf['calendar.']['timeZone'] )
            date_default_timezone_set( $this->conf['calendar.']['timeZone'] );

        $first_of_month = mktime( 0, 0, 0, $month, 1, $year );

        # remember that mktime will automatically correct if invalid dates are entered
        # for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
        # this provides a built in "rounding" feature to generate_calendar()

        $day_names = array(); #generate all the day names according to the current locale

        #January 4, 1970 was a Sunday
        for( $n = 0, $t = ( 3 + $first_day ) * 86400 - date('Z',0) ; $n < 7 ; $n++ , $t += 86400 ) {

            #%A means full textual day name
            $day_names[$n] = ucfirst( strftime( '%A', $t) );

        }

        list($month, $year, $month_name, $weekday) = explode(',',strftime('%m,%Y,%B,%w',$first_of_month));
        $weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day

        $title = $this->convertSpecialCharacters( ucfirst( $month_name ) ) . '&nbsp;' . $year;

        #note that some locales don't capitalize month and day names
        $this->listHeader = $this->convertSpecialCharacters( ucfirst( strftime( $this->conf['listView.']['strftime.']['main'], $first_of_month ) ) );

        #Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
        @list( $p, $pl ) = each( $pn );
        @list($n, $nl) = each( $pn ); #previous and next links, if applicable

        $previousImage = $previousImageDisabled = $p;
        $nextImage = $n;

        // Render navigation links as images
        if ( $this->conf['calendar.']['renderNavigationAsImages'] ) {

            $arrowLeft = str_replace( PATH_site, '', t3lib_div::getFileAbsFileName( $this->conf['file.']['arrowLeft'] ) );
            $arrowLeftDisabled = str_replace( PATH_site, '', t3lib_div::getFileAbsFileName( $this->conf['file.']['arrowLeftDisabled'] ) );
            $arrowRight = str_replace( PATH_site, '', t3lib_div::getFileAbsFileName( $this->conf['file.']['arrowRight'] ) );

            $previousImage = $this->cObj->cImage( $arrowLeft, $this->conf['calendar.']['imageArrows.']);
            $previousImageDisabled = $this->cObj->cImage( $arrowLeftDisabled, $this->conf['calendar.']['imageArrows.']);
            $nextImage = $this->cObj->cImage( $arrowRight, $this->conf['calendar.']['imageArrows.']);

        }


        // gregory goidin - rvvn
        // now, we don't link the previous month in calendar if hideIfInPast = 1 and previous month is a past month

        // TODO: This is a duplicate at query builder.
        $currentTime = time();
        $calendarStartMonth = strtotime( $this->calendarYear . '-' . $this->calendarMonth . '-01' );
        $calendarEndMonth = mktime( 23, 59, 59, intval( $this->calendarMonth ), date( 't', $calendarStartMonth ), intval( $this->calendarYear ) );


        if ( $p && ( ( $this->conf['calendar.']['hideIfInPast'] == 1
                        && $currentTime <= $calendarStartMonth )
                || $this->conf['calendar.']['hideIfInPast'] == 0 ) ) {
            $p = ( $pl ? '<a href="' . $pl . '" title="'.$this->pi_getLL("calPrev").'">' . $previousImage . '</a>' : $previousImage );
        } else { $p = $previousImageDisabled; }


        if ( $n ) $n = ( $nl ? '<a href="' . $nl . '" title="'.$this->pi_getLL("calNext").'">' . $nextImage . '</a>' : $nextImage);

        // Thanks to Patrick Gaumond and his team for the fix on month display.
        $calendar = '<table class="calendar-table" cellpadding="0" cellspacing="0">' . "\n" .
                "\t\t\t"	    . '<tr>' . "\n" .
                "\t\t\t\t"	    . '<td class="columPrevious">' . $p . '</td>' . "\n" .
                "\t\t\t\t"	    . '<td colspan="5" class="columYear">' . "\n" .
                "\t\t\t\t\t"    . ( ( $month_href && (int) $this->monthLinkDisplay == 1 ) ? '<a href="' . $this->convertSpecialCharacters( $month_href ) . '" title="' . $this->pi_getLL( "calYear" ) . ' - ' . $title . '">' . $this->listHeader . '</a>' : $this->listHeader ) . "\n" .
                "\t\t\t\t"	    . '</td>' . "\n" .
                "\t\t\t\t"	    . '<td class="columNext">' . $n . '</td>' . "\n" .
                "\t\t\t"	    . '</tr>' . "\n" .
                "\t\t\t"	    . '<tr>' . "\n";

        foreach( $day_names as $d ) {
            // ACTIVE SOLUTION Software AG
            // Use the correct byte length for the given character count (see t3lib_cs and tslib_fe) using csConvObj.
            $convertDay = ( $day_name_length <= 4 ? $GLOBALS['TSFE']->csConvObj->substr( $GLOBALS['TSFE']->renderCharset, $d, 0, $day_name_length ) : $d );
            $calendar .= "\t\t\t\t" . '<th abbr="'.$this->convertSpecialCharacters($d).'">'.$this->convertSpecialCharacters( $convertDay ) . '</th>' . "\n";
        }

        $calendar .= "\t\t\t" . '</tr>' . "\n";
        $calendar .= "\t\t\t" . '<tr>'	. "\n";

        if( $weekday > 0 ) {
            $calendar .= "\t\t\t\t" . '<td colspan="'.$weekday.'">&nbsp;</td>' . "\n"; #initial 'empty' days
        }

        for( $day=1, $days_in_month=date('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++) {

            if( $weekday == 7 ) {
                $weekday   = 0; #start a new week
                $calendar .= "\t\t\t" . '</tr>' . "\n";
                $calendar .= "\t\t\t" . '<tr>' . "\n";
            }

            // Changed render of link for compliance with context menu
            // RICC begin -> exclude past events from calendar if set in TS
            $today = date("Ymd");
            $myDay = strlen($day)==1?'0'.$day:$day;
            $renderDay = $year . $month . $myDay;



            // gregory goidin
            // no need of hidding events with start date < present day and end date > present day

            if ( isset( $days[$day]  ) and is_array( $days[$day] ) ) {


                @list($link, $classes, $content) = $days[$day];
                if(is_null($content))  $content  = (strlen($day)==1?'0'.$day:$day);
                $calendar .= "\t\t\t\t" . '<td>' .
                        ( $link ? '<div id="'.$link.'" class="'.$this->convertSpecialCharacters($classes).'">' . $content . '</div>' : $content) . '</td>' . "\n";
            }
            else {
                $newDay = (strlen($day)==1?'0'.$day:$day);

                if ($this->thisDay==$newDay && $this->thisYear==$year && $this->thisMonth==$month) {
                    $calendar .= "\t\t\t\t" . '<td><div class="linked_today_nolink" >'.$newDay.'</div></td>' . "\n";
                }else {
                    $calendar .= "\t\t\t\t" . '<td>' . $newDay . '</td>' . "\n";
                }
            }
        }

        if( $weekday != 7 ) $calendar .= "\t\t\t\t" . '<td colspan="'.(7-$weekday).'">&nbsp;</td>' . "\n"; #remaining "empty" days

        if ( $this->monthLinkDisplay == 1 ) {

            $calendar .= "\t\t\t"	    . '</tr>' . "\n";
            $calendar .= "\t\t\t"	    . '<tr>' . "\n";
            $calendar .= "\t\t\t\t"	    . '<td colspan="7" class="bottomMonthLink">' ."\n";
            $calendar .= "\t\t\t\t\t"       . '<a href="' . $this->convertSpecialCharacters( $month_href ) . '" title="' . $this->pi_getLL( "calYear" ) . ' - ' . $title.'">' . $this->pi_getLL( "calYear" ) . '</a>' . "\n";
            $calendar .= "\t\t\t\t"	    . '</td>' . "\n";
            $calendar .= "\t\t\t"	    . '</tr>' . "\n";


        } else {

            $calendar .= "\t\t\t"	    . '</tr>' . "\n";

        }

        return $calendar . "\t\t" . '</table>' . "\n";
    }


    /**
     * Shows a list of database entries
     *
     * @param    string        $content: content of the PlugIn
     * @param    array        $conf: PlugIn Configuration
     * @return    HTML list of table entries
     */
    function listView() {

        $this->conf['pidList']		= intval($this->piVars['startingPoint']);
        $this->conf['recursive']	= intval($this->piVars['recursion']);
        $this->pi_loadLL();	// Loading the LOCAL_LANG values

        if ( ! isset($this->piVars['pointer'] ) ) {
            $this->piVars['pointer']=0;
        }
        if ( ! isset($this->piVars['mode'] ) ) {
            $this->piVars['mode']=1;
        }

        // Put the whole list together:
        $fullTable			= '';	// Clear var;
        $templateMarker                 = '###NEWSCALENDAR_LISTVIEW###';
        $template			= $this->cObj->getSubpart($this->listViewTemplate, $templateMarker);
        $marker['###HEADER###']         = $this->listHeader;

        $totalResults = $this->resultListCount;

        if ( $totalResults > 0 ) {
            $marker['###BODY###']=
                    // Adds the whole list table
                    $this->makelist();
        } else {
            $marker['###BODY###']=$this->pi_getLL("noResults");
        }

        $fullTable .= $this->cObj->substituteMarkerArray( $template, $marker );

        // Returns the content from the plugin.
        return $fullTable;
    }

    /**
     * Creates a list from a database query listinterval type
     *
     * @param    ressource    $res: A database result ressource
     * @return    A HTML list if result items
     */
    function makelist() {

        $templateMarker	= '###NEWSCALENDAR_LISTVIEW_ITEM_HEADER###';
        $template	= $this->cObj->getSubpart($this->listViewTemplate, $templateMarker);

        $out = '<div'.$this->pi_classParam('listrow').'>';
        reset( $this->resultList );
        $checkDate = 0;
        while ( list( $key, $val) = each( $this->resultList ) ) {

            $recordDate = date('Y-m-d',$val['datetime']);

            $this->internal['currentRow'] = $val;
            $theListItem = $this->makeListItemNormal( 'listinterval' );

            if ($recordDate != $checkDate && $theListItem && $theListItem != '') {

                $dayHeader = $this->convertSpecialCharacters(strftime($this->conf['listView.']['strftime.']['header'],$val['datetime']));
                $marker['###HEADER###'] = '<div class="'.$this->extKey.'_dayHeader">'.$dayHeader.'</div>';
                $out .= $this->cObj->substituteMarkerArray($template,$marker);

            }

            $out		.= $theListItem;
            $checkDate	= date('Y-m-d',$val['datetime']);

        }
        $out .= '</div>';
        return $out;
    }


    // Taken from: http://pt2.php.net/manual/en/function.get-html-translation-table.php
    function xmlEntities($s) {
        //build first an assoc. array with the entities we want to match
        $table1 = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);

        //now build another assoc. array with the entities we want to replace (numeric entities)
        foreach ($table1 as $k=>$v) {
            $table1[$k] = "/$v/";
            $c = $this->convertSpecialCharacters($k,ENT_QUOTES,"UTF-8");
            $table2[$c] = "&#".ord($k).";";
        }

        //now perform a replacement using preg_replace
        //each matched value in array 1 will be replaced with the corresponding value in array 2
        $s = preg_replace($table1,$table2,$s);
        return $s;
    }

    // Original: http://blog.edrackham.com/php/get-days-between-two-dates-using-php/
    function GetDays($sStartDate, $sEndDate, $id) {
        if ($sStartDate > $sEndDate) {
            echo "NEWSCALENDAR ERROR: Startdate > EndDate<br />";
            echo "DETECTED ON NEWS RECORD UID - ".$id."<br />";
            echo "\"You died\" Elmar Hinz";
            die();
        }
        // Firstly, format the provided dates.
        // This function works best with YYYY-MM-DD
        // but other date formats will work thanks
        // to strtotime().
        $sStartDate = date("Y-m-d", strtotime($sStartDate));
        $sEndDate = date("Y-m-d", strtotime($sEndDate));

        // Start the variable off with the start date
        $aDays[] = $sStartDate;

        // Set a ‘temp’ variable, sCurrentDate, with
        // the start date - before beginning the loop
        $sCurrentDate = $sStartDate;

        // While the current date is less than the end date
        while($sCurrentDate < $sEndDate) {
            // Add a day to the current date
            $sCurrentDate = date("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));

            // Add this new day to the aDays array
            $aDays[] = $sCurrentDate;
        }

        // Once the loop has finished, return the
        // array of days.
        return $aDays;
    }

    // Original: http://www.the-art-of-web.com/php/sortarray/
    function compare_datetime($a, $b) {
        return strnatcmp($a['datetime'], $b['datetime']);
    }

    /* ******************************************************
     * * Render list normal view                            *
     *******************************************************/

    /**
     * Shows a list of database entries
     *
     * @param    string        $content: content of the PlugIn
     * @param    array        $conf: PlugIn Configuration
     * @return    HTML list of table entries
     */

    function listViewNormal() {


        $this->conf['pidList']	= intval($this->piVars['startingPoint']);
        $this->conf['recursive']= intval($this->piVars['recursion']);
        $this->pi_loadLL();        // Loading the LOCAL_LANG values

        if (!isset($this->piVars['pointer'])) {
            $this->piVars['pointer']=0;
        }
        if (!isset($this->piVars['mode'])) {
            $this->piVars['mode']=1;
        }

        // Initializing the query parameters:
        // Number of results to show in a listing.
        $this->internal['results_at_a_time'] = $this->conf['pageBrowser.']['limit'];
        $this->internal['maxPages'] = $this->conf['pageBrowser.']['maxPages'];
        $this->internal['descFlag']= $this->conf['pageBrowser.']['order'];
        $this->internal['orderByList']='datetime';
        $this->internal['orderBy'] = 'datetime';
        $this->internal['pagefloat'] = $this->conf['pageBrowser.']['pagefloat'];
        $this->internal['showFirstLast']=$this->conf['pageBrowser.']['showFirstLast'];
        $this->internal['showRange']=$this->conf['pageBrowser.']['showRange'];
        $this->internal['dontLinkActivePage'] = $this->conf['pageBrowser.']['dontLinkActivePage'];


        // gregory goidin - rvvn
        // We set the total records in the pageBrowser, we need the global count without the limit parameter of the sqlquery
        $totalResults = $this->globalResCount;
        $this->internal['res_count'] = $totalResults;



        // gregory goidin - rvvn
        // cause bug in page browser so just comment the line

        // Make listing query, pass query to SQL database:
        $res = $this->globalRes;
        $this->internal['currentTable'] = 'tt_news';

        // Put the whole list together:

        $fullTable = '';    // Clear var;
        $marker = array();
        $wrapped = array();
        $templateMarker = '###NEWSCALENDAR_LISTVIEW###';
        $template = $this->cObj->getSubpart( $this->listViewTemplate, $templateMarker );
        $marker['###HEADER###'] = $this->listHeader;


        // Add a page browser
        $this->browserLanguage();


        // gregory goidin - rvvn
        // if the pageBrowser.show is not active, hide the pageBrowser
        if ( intval( $this->conf['pageBrowser.']['show'] ) == 0 && $totalResults > 0 ) {
            $marker['###BODY###']=$this->makelistNormal( $res );

        }
        else if ( $totalResults > 0 ) {
            $marker['###BODY###']=
                    // Adds the whole list table
                    $this->makelistNormal($res).
                    // Add template engine for rendering list view

                    // gregory goidin - rvvn
                    // just add the pointer parameter
                    ( $totalResults>$this->conf['pageBrowser.']['limit'] ?
                            $this->pi_list_browseresults( $this->conf['pageBrowser.']['showResultCount'], $this->conf['pageBrowser.']['tableParams'], $this->wrapArr, $this->conf['pageBrowser.']['pointer'] ) :
                            null);

        }else {
            $marker['###BODY###']=$this->pi_getLL("noResults");
        }

        $fullTable.=$this->cObj->substituteMarkerArray($template,$marker);

        // Returns the content from the plugin.
        return $fullTable;

    }

    /**
     * Adds language translation to the result search browser
     *
     * @param    string        $content: content of the PlugIn
     * @param    array         $conf: PlugIn Configuration
     * @return   void
     */
    function browserLanguage() {
        $wrapArrFields = explode(',', 'disabledLinkWrap,inactiveLinkWrap,activeLinkWrap,browseLinksWrap,showResultsWrap,showResultsNumbersWrap,browseBoxWrap');
        $this->wrapArr = array();
        foreach($wrapArrFields as $key) {
            if ($this->conf['pageBrowser.'][$key]) {
                $this->wrapArr[$key] = $this->conf['pageBrowser.'][$key];
            }
        }
        if ($this->wrapArr['showResultsNumbersWrap'] && strpos($this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_displays'],'%s')) {
            // if the advanced pagebrowser is enabled and the "pi_list_browseresults_displays" label contains %s it will be replaced with the content of the label "pi_list_browseresults_displays_advanced"
            $this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_displays'] = $this->LOCAL_LANG[$this->LLkey]['pi_list_browseresults_displays_advanced'];
        }
    }

    /**
     * Creates a list from a database query
     *
     * @param    ressource    $res: A database result ressource
     * @return    A HTML list if result items
     */
    function makelistNormal($res) {
        $items=array();
        // Make list table rows
        while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $items[]=$this->makeListItemNormal();
        }

        $out = '<div'.$this->pi_classParam('listrow').'>
            '.implode(chr(10),$items).'
            </div>';
        return $out;
    }

    /**
     * Implodes a single row from a database to a single line
     *
     * @return    Imploded column values
     */
    function makeListItemNormal( $type = 'list' ) {

        $this->piVars['tt_news'] = $this->getFieldContent('uid');
        $this->piVars['backPid'] = $this->backPage;

        $marker=array();
        $wrapped=array();

        $templateMarker = '###NEWSCALENDAR_LISTITEM###';
        switch( $type ) {
            case 'listinterval':
                $templateMarker = '###NEWSCALENDAR_LISTITEM_INTERVAL###';
                break;
            case 'calendar':
                $templateMarker = '###NEWSCALENDAR_CALENDARITEM###';
                break;
        }

        if ( $type == 'calendar' ) {
            $template = $this->cObj->getSubpart( $this->calendarViewTemplate, $templateMarker );
        } else {
            $template = $this->cObj->getSubpart( $this->listViewTemplate, $templateMarker );
        }

        $marker['###URL###'] = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array(),$cache=$this->cacheAdd,$clearAnyway=0,$this->singleView);
        // Change by Ricc, date: 20130423; bug: #8232
        //$marker['###TARGET###'] = '';
        $marker['###TARGET###'] = $GLOBALS['TSFE']->config['config']['intTarget'];
        $marker['###IMAGE###'] = '';
        $marker['###NEWS_SUBHEADER###'] = '';


        // Set special news type context menu link (local page)
        if ( $this->getFieldContent('type') == 1 ) {
            $this->pi_linkTP('dummy',$urlParameters=array(),$cache=$this->cacheAdd, $this->getFieldContent('page'));
            $marker['###URL###'] = $this->cObj->lastTypoLinkUrl;
        }

        // Set special news type context menu link (external url)
        if ( $this->getFieldContent('type') == 2 ) {
            $pieces = explode(" ", $this->getFieldContent('ext_url') );
            $target = $pieces[1];
            $url = $this->cObj->getTypoLink_URL( $pieces[0]);
            $marker['###URL###'] = $url;
            $marker['###TARGET###'] = $target;
        }
        $marker['###DATETIME###'] = $this->convertSpecialCharacters(strftime($this->conf['listView.']['strftime.']['header'],$this->getFieldContent('datetime')));

        // gregory goidin - rvvn
        // Add one marker to allow the use of startdate, if there's a enddate, concat the two date with a locallang value "dateToDate"
        if($this->getFieldContent('tx_newscalendar_calendardate')!=0) {
            $marker['###STARTENDATE###'] = $this->convertSpecialCharacters(strftime($this->conf['listView.']['strftime.']['header'],$this->getFieldContent('tx_newscalendar_calendardate')));
            $marker['###DAYNB###'] = $this->convertSpecialCharacters(trim(strftime("%e",$this->getFieldContent('tx_newscalendar_calendardate'))));
            if ($this->getFieldContent('tx_newscalendar_calendardate') != $this->getFieldContent('tx_newscalendar_calendardate_end') && $this->getFieldContent('tx_newscalendar_calendardate_end') !=0) {
                $marker['###STARTENDATE###'].=" ".$this->pi_getLL("dateToDate")." ".$this->convertSpecialCharacters(strftime($this->conf['listView.']['strftime.']['header'],$this->getFieldContent('tx_newscalendar_calendardate_end')));
            }
        }
        else {
            $marker['###STARTENDATE###']=$marker['###DATETIME###'];
            $marker['###DAYNB###'] = $this->convertSpecialCharacters(trim(strftime("%e",$this->getFieldContent('datetime'))));
        }


        $marker['###TITLE###'] = $this->getFieldContent('title');
        $marker['###IMAGE###'] = $this->makeListImageCode( $this->getFieldContent('image'), $type );

        if($this->getFieldContent('short') != '') {
            $marker['###NEWS_SUBHEADER###'] = $this->cObj->stdWrap($this->getFieldContent('short'), $this->conf['listView.']['subheader_stdWrap.']);
        } else if ($this->getFieldContent('short')=='' && $this->getFieldContent('bodytext') != '') {
            $marker['###NEWS_SUBHEADER###'] = $this->cObj->stdWrap($this->getFieldContent('bodytext'), $this->conf['listView.']['subheader_stdWrap.']);
        }


        // Special calendar view options
        if ( $type == 'calendar' ) {
            $marker['###DATETIME###'] = $this->convertSpecialCharacters(strftime($this->conf['calendar.']['strftime.']['contextItem'],$this->getFieldContent('datetime')));
        }

        $out = $this->cObj->substituteMarkerArray( $template, $marker );

        return $out;
    }

    /**
     * Returns the content of a given field
     *
     * @param    string        $fN: name of table field
     * @return    Value of the field
     */
    function getFieldContent($fN) {
        switch($fN) {
            default:
                return $this->internal['currentRow'][$fN];
                break;
        }
    }
    /**
     * Returns the label for a fieldname from local language array
     *
     * @param    [type]        $fN: ...
     * @return    [type]        ...
     */
    function getFieldHeader($fN) {
        switch($fN) {

            default:
                return $this->pi_getLL('listFieldHeader_'.$fN,'['.$fN.']');
                break;
        }
    }

    /**
     * Returns a sorting link for a column header
     *
     * @param    string        $fN: Fieldname
     * @return    The fieldlabel wrapped in link that contains sorting vars
     */
    function getFieldHeader_sortLink($fN) {
        return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array('sort'=>$fN.':'.($this->internal['descFlag']?0:1)));
    }


    function convertSpecialCharacters($string) {
        switch ($this->parserFunction) {
            case 'htmlspecialchars':
                return htmlspecialchars($string, ENT_COMPAT, $GLOBALS['TSFE']->tmpl->setup['config.']['renderCharset']);
            case 'skip':
                return $string;
            default:
                return htmlentities($string, ENT_QUOTES, $GLOBALS['TSFE']->tmpl->setup['config.']['renderCharset']);
        }
    }

    // RICC begin
    // get the image html code via t3 api funcs.
    /**
     * Returns the html for the image(s) in the listView
     *
     * @param    string  $imgFieldContent: Content of the image field
     * @return   string  the whole img code
     */
    function makeListImageCode ( $imgFieldContent, $type = null ) {

        // dam_ttnews compatible, no extra configuration needed
        // sponsored by phase2-networks.com
        $damOn = t3lib_extMgm::isLoaded( $key = 'dam_ttnews' );
        if ( $damOn ) {
            $damFiles = tx_dam_db::getReferencedFiles('tt_news', $this->getFieldContent('uid'), 'tx_damnews_dam_images');
            if ( is_array( $damFiles['files'] ) )
                $image = current($damFiles['files']);
        }

        if ( ! $imgFieldContent && (! $image && $damOn ) ) {
            return '';
        } else {
	    $altText="";
	    $titleText="";  
	    $caption="";
	    $caption= explode(chr(10), $this->getFieldContent('imagecaption'));
            $caption = $caption[0];           
	    $altText = explode(chr(10), $this->getFieldContent('imagealttext'));
            $altText = $altText[0];
            $titleText = explode(chr(10), $this->getFieldContent('imagetitletext'));
            $titleText = $titleText[0];
            //rvvn : gregory goidin : allow the user to use some data in TS => images titleText
    	    $this->cObj->data['tx_newscalendar_caption'] = $caption;
            if ( ! $damOn ) {
                $imagesArray = explode(",", $imgFieldContent);
                $image = $this->uploadFolder . $imagesArray['0'];
            }
            if ( $type != 'calendar' ) {
            //rvvn : gregory goidin : add image alttext and titletext
            	$conf=$this->conf['listView.']['image.'];
            	$conf['altText']=$altText;
            	$conf['titleText']=$titleText;
                $imgCode = $this->cObj->cImage($image,$conf);              

            } else {
            //rvvn : gregory goidin : add image alttext and titletext
            	$conf=$this->conf['calendar.']['image.'];
            	$conf['altText']=$altText;
            	$conf['titleText']=$titleText;
                $imgCode = $this->cObj->cImage($image, $conf);


            }
            
            return $imgCode;
        }
    }
    // RICC end

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newscalendar/pi1/class.tx_newscalendar_pi1.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newscalendar/pi1/class.tx_newscalendar_pi1.php']);
}

?>
