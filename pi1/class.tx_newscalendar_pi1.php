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

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'News Calendar' for the 'newscalendar' extension.
 *
 * @author	Philip Almeida <philip.almeida@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_newscalendar
 */
class tx_newscalendar_pi1 extends tslib_pibase {


	var $prefixId      = 'tx_ttnews';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_newscalendar_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'newscalendar';	// The extension key.
	var $pi_checkCHash = true;

	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{

		global $LANG;	

		$this->conf=$conf;
		$this->content=$content;
		$this->pi_initPIflexForm();	// Init FlexForm configuration for plugin
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=0;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

#		$this->initLanguages();

		/*
		* Retrieve day vars
		*/  
		$this->time = time();
		$this->thisDay = date('j',time());
		$this->thisYear = date('Y',time());
		$this->thisMonth = date('n',time());

		/*
		* Retrieve conf variables for css definitions
		*/  
		$this->cssCalendar = ($this->conf['file.']['cssCalendar']=='EXT:newscalendar/res/calendar.css'?t3lib_extMgm::extRelPath('newscalendar').'res/cssCalendar.css':$this->conf['file.']['cssCalendar']);

		$this->cssContextMenu = ($this->conf['file.']['cssContextMenu']=='EXT:newscalendar/res/cssContextMenu.css'?t3lib_extMgm::extRelPath('newscalendar').'res/cssContextMenu.css':$this->conf['file.']['cssContextMenu']);


		$this->content = '<link href="'.$this->cssCalendar.'" rel=STYLESHEET type="text/css">
		<link href="'.$this->cssContextMenu.'" rel=STYLESHEET type="text/css">';



		/*
		* Set template file for list view
		*/

		$this->listViewTemplate = t3lib_div::getURL( ($this->conf['file.']['listView.']['listViewTemplate']=='EXT:newscalendar/res/listViewTemplate.html'?substr(t3lib_extMgm::extRelPath('newscalendar').'res/listViewTemplate.html',3):$this->conf['file.']['listView.']['listViewTemplate']));

		/*
		* Retrieve Flexform variables
		*/

		$this->displayType = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'displayType', 'sDEF');
		$this->monthLinkDisplay = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'monthLinkDisplay', 'sDEF');
		$this->listPage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listPage', 'sDEF');
		$this->singleView = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'destinationPage', 'sDEF');
		$this->backPage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'backPage', 'sDEF');
		$this->startingPoint = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pidList', 'sDEF');
		$this->recursion = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursion', 'sDEF');
		$this->dayNameLength = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dayNameLength', 'sDEF');
		$this->contextMenuLink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'contextMenuLink', 'sDEF');

		/*
		* Get the PID from which to make the menu.
		*/

		if ($this->startingPoint) {
			// If a page is set as reference in the 'Startingpoint' field, use that
			$this->menuPid = $this->startingPoint;
		}else{
			// Otherwise use the page's id-number from TSFE
			$this->menuPid = $GLOBALS['TSFE']->id;
		}


		// Define Calendar rendering dates
		$this->calendarYear = $this->piVars['calendarYear'] ? $this->piVars['calendarYear']:$this->thisYear;
		$this->calendarMonth = $this->piVars['calendarMonth']?$this->piVars['calendarMonth']:$this->thisMonth;

		/*
		* Lang configuration
		*/
		setlocale(LC_ALL, $GLOBALS['TSFE']->tmpl->setup['config.']['locale_all']);

		#$LANG->init($GLOBALS['TSFE']->tmpl->setup['config.']['language']);
		
			#return 'ii'; //$this->pi_wrapInBaseClass(
		return $this->content.$this->calendarQuery();
		
	}

	/**
	 * The calendarQuery method of the PlugIn
	 *
	 * @param	int		$year: The year for the calendar
	 * @param	int		$month: The month for the calendar
	 * @return	The content for the calendar that is displayed on the website
	 */ 
	function calendarQuery()	{
		global $TSFE;

		// Define the list of pages to search for recently changed content (999 depth level).
		$this->search_list = $this->pi_getPidList($this->menuPid,$this->recursion);
		// $GLOBALS["TSFE"]->sys_language_uid != 0

		$this->previousNews = $this->piVars['tt_news'];


		$this->where = 'deleted=0
			  AND hidden=0
			  AND sys_language_uid='.$GLOBALS["TSFE"]->sys_language_uid.'
			  AND FROM_UNIXTIME(datetime,"%Y")='.$this->calendarYear.'
			  AND FROM_UNIXTIME(datetime,"%c")='.$this->calendarMonth;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
	                'uid, title, FROM_UNIXTIME(datetime,"%e")',
	                'tt_news',
	            	$this->where.' AND pid in ('.$this->search_list.')',
	                '',
	                'datetime ASC',
	                ''
		);


		$contextScript .= '
		<script type="text/javascript" src="'.substr(t3lib_extMgm::extRelPath('newscalendar'),3).'res/rightcontext.js"></script>

		<script type="text/javascript">
		';

		$contextScript.= "
		RightContext.menuTriggerEvent = '".$this->contextMenuLink."';
		";

		/*
		* Build Links for news items
		*/
		$cid=0;
		$newslist='';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
			if ($row[2]!=$cid){
				if ($cid!=0){
					$contextScript.= $this->buildContextMenu($cid, $newslist);
				}
				$newslist='';
				$cid=$row[2];
			}
			if ($newslist==''){
				$newslist.=$row[0];
			}else{
				$newslist.=','.$row[0];
			}

		}
		if ($newslist!=''){
			$contextScript.= $this->buildContextMenu($cid, $newslist);
		}

		$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
	                "distinct FROM_UNIXTIME(datetime,'%e')",
	                'tt_news',
	            	$this->where.' AND pid in ('.$this->search_list.')',
	                '',
	                'datetime ASC',
	                ''
		);


		while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_row($res2)) {

			$contextScript .= '
			// add menuX to the menu collection
			RightContext.addMenu("idMenu'.$row2[0].'", menu'.$row2[0].');
			';

			if ($this->thisDay==$row2[0] && $this->thisYear==$this->calendarYear && $this->thisMonth==$this->calendarMonth){
				$daysLinkArray[$row2[0]] = array('idMenu'.$row2[0],'linked_today');
			}else{
				$daysLinkArray[$row2[0]] = array('idMenu'.$row2[0],'linked_day');
			}
		}


		$contextScript .= "
			// initialize RightContext
			RightContext.initialize();
			</script>
		";

		/*
		* Set tt_news to the previous id so it sticks on navigation.
		*/
		$this->piVars['tt_news'] =$this->previousNews;

		/*
		* Reset pointer.
		*/
		$this->previousNews=$this->piVars['pointer'];
		$this->piVars['pointer']=null;

		/*
		* Set date values for previous link.
		*/		
		$this->piVars['calendarYear'] =($this->calendarMonth==1?$this->calendarYear-1:$this->calendarYear);
		$this->piVars['calendarMonth'] = ($this->calendarMonth==1?12:$this->calendarMonth-1);

		/* 
		* Build previous link  
		*/
		$this->linkPrev = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array(),$cache=0,$clearAnyway=0);

		/*
		* Set date values for next link.
		*/		
		$this->piVars['calendarYear'] =($this->calendarMonth==12?$this->calendarYear+1:$this->calendarYear);
		$this->piVars['calendarMonth'] = ($this->calendarMonth==12?1:$this->calendarMonth+1);
		
		/* 
		* Build next link  
		*/
		$this->linkNext = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array(),$cache=0,$clearAnyway=0);

		/*
		* Reset date values. 
		*/		
		$this->piVars['calendarYear'] = $this->calendarYear;
		$this->piVars['calendarMonth'] = $this->calendarMonth;

		/*
		* If $this->listPage is set then we will build calendar with link to month view.
		*/

		if ($this->listPage){

			$this->piVars['startingPoint'] = $this->startingPoint;
			$this->piVars['recursion'] = $this->recursion;

			$this->linkList = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array(),$cache=0,$clearAnyway=0, $this->listPage);
		}

		/*
		* Reset pointer.
		*/
		$this->piVars['pointer']=$this->previousNews;

		$this->np = array('&laquo;' => $this->linkPrev,'&raquo;' => $this->linkNext);


		$calendar = $this->generate_calendar($this->calendarYear, $this->calendarMonth, $daysLinkArray, $this->dayNameLength,($this->listPage?$this->linkList:NULL),0,$this->np);
		#return $this->pi_wrapInBaseClass($content);

		if($this->displayType==1)
			return $this->pi_wrapInBaseClass($calendar.$contextScript);
		else
			return $this->pi_wrapInBaseClass($this->listView($this->content,$this->conf));
	}	

	/**
	 * The buildContextMenu method of the PlugIn
	 *
	 * @param	int		$day: The day for news
	 * @param	varchar		$newslist: The list (comma separated value) of news to build the context menu
	 * @return	The javascript to build menus
	 */
	function buildContextMenu($day, $newslist)	{

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
	                'news.title, news.datetime, news.uid',
	                'tt_news news',
	            	'news.uid IN ('.$newslist.')',
	                '',
	                'news.datetime DESC',
	                ''
	            );			

		#$displayDate = strftime('%Y-%m-%d', row[1]);
		$displayDate = mktime(0, 0, 0, $this->calendarMonth, $day, $this->calendarYear);

#calendar.contextHeader.strftime


		$displayDate = htmlentities(strftime($this->conf['calendar.']['contextHeader.']['strftime'], $displayDate));

		#$displayDate = $this->calendarYear.'-'.$this->calendarMonth.'-'.$day;

		$contextMenuScript = '
			// and another menu
			menu'.$day.' = { attributes: "x,y" ,
			
				items: [ 
					{type:RightContext.TYPE_TEXT,
					text: "'.$displayDate.'"},
';
		$rowCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		$rowLine = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
			$rowLine++;
			$newsTime = htmlentities(strftime($this->conf['calendar.']['contextItem.']['strftime'], $row[1])).'&nbsp;-&nbsp;'.$row[0];

			$this->piVars['tt_news'] = $row[2];
			$this->piVars['backPid'] = $this->backPage;
			
			$url = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array(),$cache=0,$clearAnyway=0,$this->singleView);

/*
			$url = $this->pi_getPageLink($this->singleView,'',
					Array ( 'tx_ttnews[tt_news]' => $row[2],
						'tx_ttnews[backPid]' => $this->backPage)); */
			$contextMenuScript .= '
					{type:RightContext.TYPE_MENU,
					text:"'.str_replace('"','\"',$newsTime).'",
					url:"'.$url.'"
					}';
			if ($rowCount<>$rowLine)
				$contextMenuScript .= ',
';
		}

		$contextMenuScript .= ']
				};
		';

		return $contextMenuScript;

	}


	# PHP Calendar (version 2.3), written by Keith Devens
	# http://keithdevens.com/software/php_calendar
	#  see example at http://keithdevens.com/weblog
	# License: http://keithdevens.com/software/license
	
	function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()){
		/*
		* Add language add-ons for calendar
		*/
		global $LANG;	


		#$LANG->includeLLFile('EXT:newscalendar/pi1/locallang.xml');


		$first_of_month = gmmktime(0,0,0,$month,1,$year);
		#remember that mktime will automatically correct if invalid dates are entered
		# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
		# this provides a built in "rounding" feature to generate_calendar()
	
		$day_names = array(); #generate all the day names according to the current locale
		for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
			$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name
		list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
		$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
		$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;

		  #note that some locales don't capitalize month and day names
		#$smallTitle = htmlentities(ucfirst($month_name));
	        $this->listHeader = strftime($this->conf['header.']['strftime'],$first_of_month);

		#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
		@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
		if($p) $p = ($pl ? '<a href="'.htmlspecialchars($pl).'" title="'.$this->pi_getLL("calPrev").'">'.$p.'</a>' : $p);
		if($n) $n = ($nl ? '<a href="'.htmlspecialchars($nl).'" title="'.$this->pi_getLL("calNext").'">'.$n.'</a>' : $n);
		$calendar = '<table class="calendar-table" cellpadding="0" cellspacing="0">'."\n".
				'<tr>'."\n".'
					<td class="columPrevious">'.$p.'</td>
					<td colspan="5" class="columYear">'."\n".'
						'.($month_href ? '<a href="'.htmlspecialchars($month_href).'" title="'.$this->pi_getLL("calYear").' - '.$title.'">'. $this->listHeader.'</a>' : $this->listHeader).'
					</td>
					<td class="columNext">'.$n.'</td>
				<tr>';
	
		if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
			#if day_name_length is >3, the full name of the day will be printed
			foreach($day_names as $d)
				$calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
			$calendar .= "</tr>\n<tr>";
		}
	
		if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
		for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
			if($weekday == 7){
				$weekday   = 0; #start a new week
				$calendar .= "</tr>\n<tr>";
			}

			// Changed render of link for compliance with context menu 

			if(isset($days[$day]) and is_array($days[$day])){
				@list($link, $classes, $content) = $days[$day];
				if(is_null($content))  $content  = (strlen($day)==1?'0'.$day:$day);
				$calendar .= '<td>'.
					($link ? '<div context="'.$link.'" class="'.htmlspecialchars($classes).'" title="'.$this->pi_getLL("readmore").' '.$content.'">'.$content.'</div>' : $content).'</td>';
			}
			else {
				$newDay = (strlen($day)==1?'0'.$day:$day);

				if ($this->thisDay==$newDay && $this->thisYear==$year && $this->thisMonth==$month){
					$calendar .= '<td><div class="linked_today_nolink" >'.$newDay.'</div></td>';
				}else{
					$calendar .= "<td>$newDay</td>";
				}
			}
		}
		if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days
	
		if ($this->monthLinkDisplay ==1){
			$calendar .='
				</tr>
				<tr>
					<td colspan="7"  class="bottomMonthLink">';
			$calendar .= '
						<a href="'.htmlspecialchars($month_href).'" title="'.$this->pi_getLL("calYear").' - '.$title.'">'.$this->pi_getLL("calYear").'</a>
					</td>
				</tr>';
		} else {
			$calendar .='</tr>';
		}

		return $calendar."</table>\n";
	}


	/**
     	* Shows a list of database entries
     	*
     	* @param    string        $content: content of the PlugIn
     	* @param    array        $conf: PlugIn Configuration
     	* @return    HTML list of table entries
     	*/
    	function listView($content,$conf)    {

		if(!$this->piVars['startingPoint']){
			return;
		};

		$this->conf['pidList']	= $this->piVars['startingPoint'];
		$this->conf['recursive']= $this->piVars['recursion'];

		#$this->conf=$conf;        // Setting the TypoScript passed to this function in $this->conf
		#$this->pi_setPiVarDefaults();
		$this->pi_loadLL();        // Loading the LOCAL_LANG values
		
		#$lConf = $this->conf['listView.'];    // Local settings for the listView function
	

		if (!isset($this->piVars['pointer']))    $this->piVars['pointer']=0;
		if (!isset($this->piVars['mode']))    $this->piVars['mode']=1;

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
	
			// Get number of records:
		$where = " AND FROM_UNIXTIME(datetime,'%c')=".$this->piVars['calendarMonth'];

		$this->whereList=' AND '.$this->where;

		$res = $this->pi_exec_query('tt_news',1, $this->whereList);

		$res4 = $this->pi_exec_query('tt_news',1, $this->whereList);

		$res4 = $this->internal['res_count'] = $GLOBALS['TYPO3_DB']->sql_fetch_row($res4);
		
		#$this->internal['res_count'] = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		$totalResults = $this->internal['res_count'][0];


		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
	
			// Make listing query, pass query to SQL database:
		$res = $this->pi_exec_query('tt_news',0,$this->whereList);
		$this->internal['currentTable'] = 'tt_news';


			// Put the whole list together:
		$fullTable='';    // Clear var;
		#$fullTable.=t3lib_div::view_array($this->piVars);    // DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!
	

		$marker=array();
		$wrapped=array();	
		$templateMarker = '###NEWSCALENDAR_LISTVIEW###';
		$template = $this->cObj->getSubpart($this->listViewTemplate, $templateMarker);
		$marker['###HEADER###']= $this->listHeader;


			// Adds the mode selector.
		#$fullTable.=$this->pi_list_modeSelector($items);
	
	
			// Adds the search box:
		#$fullTable.=$this->pi_list_searchBox();

			// Add a page browser
		$this->browserLanguage();

		if ($totalResults>0){

			$marker['###BODY###']=
		
				// Adds the whole list table
			$this->makelist($res).
		
				// Add template engine for rendering list view
			($totalResults>$this->conf['pageBrowser.']['limit']?$this->pi_list_browseresults($this->conf['pageBrowser.']['showResultCount'], $this->conf['pageBrowser.']['tableParams'],$this->wrapArr):null);
		}else{
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

	function browserLanguage(){

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
    function makelist($res)    {

        $items=array();
            // Make list table rows
        while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))    {
            $items[]=$this->makeListItem();
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
    function makeListItem()    {

	$this->piVars['tt_news'] = $this->getFieldContent('uid');
	$this->piVars['backPid'] = $this->backPage;

	$marker=array();
	$wrapped=array();	
	$templateMarker = '###NEWSCALENDAR_LISTITEM###';
	$template = $this->cObj->getSubpart($this->listViewTemplate, $templateMarker);

	$marker['###URL###'] = $this->pi_linkTP_keepPIvars_url($overrulePIvars=array(),$cache=0,$clearAnyway=0,$this->singleView);

	$marker['###DATETIME###']=  htmlentities(strftime($this->conf['listView.']['header.']['strftime'],$this->getFieldContent('datetime')));

	$marker['###TITLE###']= $this->getFieldContent('title');
	/* date('j',time());
	$out='
                <p'.$this->pi_classParam('listrowField-tester').'>'.$this->getFieldContent('title').'</p>
            ';
	*/
	$out = $this->cObj->substituteMarkerArray($template,$marker);
        return $out;
    }
    /**
     * Display a single item from the database
     *
     * @param    string        $content: The PlugIn content
     * @param    array        $conf: The PlugIn configuration
     * @return    HTML of a single database entry
     */
    function singleView($content,$conf)    {
        $this->conf=$conf;
        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
        
    
            // This sets the title of the page for use in indexed search results:
        if ($this->internal['currentRow']['title'])    $GLOBALS['TSFE']->indexedDocTitle=$this->internal['currentRow']['title'];
    
        $content='<div'.$this->pi_classParam('singleView').'>
            <H2>Record "'.$this->internal['currentRow']['uid'].'" from table "'.$this->internal['currentTable'].'":</H2>
            <table>
                <tr>
                    <td nowrap="nowrap" valign="top"'.$this->pi_classParam('singleView-HCell').'><p>'.$this->getFieldHeader('tester').'</p></td>
                    <td valign="top"><p>'.$this->getFieldContent('tester').'</p></td>
                </tr>
                <tr>
                    <td nowrap'.$this->pi_classParam('singleView-HCell').'><p>Last updated:</p></td>
                    <td valign="top"><p>'.date('d-m-Y H:i',$this->internal['currentRow']['tstamp']).'</p></td>
                </tr>
                <tr>
                    <td nowrap'.$this->pi_classParam('singleView-HCell').'><p>Created:</p></td>
                    <td valign="top"><p>'.date('d-m-Y H:i',$this->internal['currentRow']['crdate']).'</p></td>
                </tr>
            </table>
        <p>'.$this->pi_list_linkSingle($this->pi_getLL('back','Back'),0).'</p></div>'.
        $this->pi_getEditPanel();
    
        return $content;
    }
    /**
     * Returns the content of a given field
     *
     * @param    string        $fN: name of table field
     * @return    Value of the field
     */
    function getFieldContent($fN)    {
        switch($fN) {
/*
            case 'uid':
                return $this->pi_list_linkSingle($this->internal['currentRow'][$fN],$this->internal['currentRow']['uid'],1);    // The "1" means that the display of single items is CACHED! Set to zero to disable caching.
            break;
*/
            
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
    function getFieldHeader($fN)    {
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
    function getFieldHeader_sortLink($fN)    {
        return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array('sort'=>$fN.':'.($this->internal['descFlag']?0:1)));
    }


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newscalendar/pi1/class.tx_newscalendar_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newscalendar/pi1/class.tx_newscalendar_pi1.php']);
}

?>