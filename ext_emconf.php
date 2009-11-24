<?php

########################################################################
# Extension Manager/Repository config file for ext: "newscalendar"
#
# Auto generated 18-11-2009 17:26
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'News Calendar',
	'description' => 'Calendar for Ext:tt_news. Provides calendar and a list view functionality. Featured sponsors: geefgratis.nl, eventonizer.nl',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.1.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_news',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Philip Almeida, Clemens Riccabona',
	'author_email' => 'philip.almeida@freedomson.com, clemens@riccabona.biz',
	'author_company' => 'freedomson.com eur-ops.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:68:{s:9:"ChangeLog";s:4:"d7b5";s:10:"README.txt";s:4:"7573";s:12:"ext_icon.gif";s:4:"f04e";s:17:"ext_localconf.php";s:4:"5708";s:14:"ext_tables.php";s:4:"77b3";s:14:"ext_tables.sql";s:4:"d348";s:15:"flexform_ds.xml";s:4:"8c5e";s:13:"locallang.xml";s:4:"2b85";s:16:"locallang_db.xml";s:4:"17cd";s:20:"static/constants.txt";s:4:"2cb6";s:16:"static/setup.txt";s:4:"970b";s:14:"pi1/ce_wiz.gif";s:4:"f04e";s:33:"pi1/class.tx_newscalendar_pi1.php";s:4:"52ce";s:41:"pi1/class.tx_newscalendar_pi1_wizicon.php";s:4:"1d8d";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"bf9b";s:24:"pi1/static/editorcfg.txt";s:4:"c760";s:14:"res/accept.png";s:4:"8bfe";s:29:"res/calendarViewTemplate.html";s:4:"23ab";s:14:"res/cancel.png";s:4:"757a";s:25:"res/class.newscalendar.js";s:4:"66c4";s:19:"res/cssCalendar.css";s:4:"5295";s:22:"res/cssContextMenu.css";s:4:"0892";s:19:"res/date_changer.js";s:4:"69b9";s:17:"res/jquery.min.js";s:4:"bb38";s:25:"res/listViewTemplate.html";s:4:"c918";s:30:"res/bt-0.9.5-rc1/CHANGELOG.txt";s:4:"86bf";s:29:"res/bt-0.9.5-rc1/jquery.bt.js";s:4:"f0ee";s:33:"res/bt-0.9.5-rc1/jquery.bt.min.js";s:4:"45ef";s:43:"res/bt-0.9.5-rc1/other_libs/jquery-1.2.6.js";s:4:"3436";s:47:"res/bt-0.9.5-rc1/other_libs/jquery-1.2.6.min.js";s:4:"a933";s:43:"res/bt-0.9.5-rc1/other_libs/jquery-1.3.2.js";s:4:"7b7e";s:47:"res/bt-0.9.5-rc1/other_libs/jquery-1.3.2.min.js";s:4:"bb38";s:45:"res/bt-0.9.5-rc1/other_libs/jquery-1.3.min.js";s:4:"35b4";s:48:"res/bt-0.9.5-rc1/other_libs/jquery.easing.1.3.js";s:4:"6516";s:58:"res/bt-0.9.5-rc1/other_libs/jquery.hoverIntent.minified.js";s:4:"015b";s:47:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/AUTHORS";s:4:"e1b4";s:47:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/COPYING";s:4:"3b83";s:46:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/README";s:4:"b7e6";s:60:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/excanvas.compiled.js";s:4:"13b2";s:51:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/excanvas.js";s:4:"c0ca";s:58:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/arc.html";s:4:"d850";s:64:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/clearpath.html";s:4:"e75e";s:64:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/drawimage.html";s:4:"98b1";s:63:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/gradient.html";s:4:"9378";s:64:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/gradient2.html";s:4:"f896";s:64:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/linewidth.html";s:4:"ffaa";s:63:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/overflow.html";s:4:"062c";s:69:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/quadraticcurve.html";s:4:"6af0";s:63:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/resizing.html";s:4:"e254";s:70:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/saverestorepath.html";s:4:"9b03";s:74:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/stroke-scale-rotate.html";s:4:"cbc0";s:83:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/testcases/stroke-should-not-close-path.html";s:4:"d0a4";s:62:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/examples/example1.html";s:4:"5e04";s:62:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/examples/example2.html";s:4:"559d";s:62:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/examples/example3.html";s:4:"9bcf";s:55:"res/bt-0.9.5-rc1/other_libs/excanvas_r3/examples/ff.jpg";s:4:"8aee";s:56:"res/bt-0.9.5-rc1/other_libs/bgiframe_2.1.1/ChangeLog.txt";s:4:"0a88";s:52:"res/bt-0.9.5-rc1/other_libs/bgiframe_2.1.1/META.json";s:4:"bd9b";s:61:"res/bt-0.9.5-rc1/other_libs/bgiframe_2.1.1/jquery.bgiframe.js";s:4:"880b";s:65:"res/bt-0.9.5-rc1/other_libs/bgiframe_2.1.1/jquery.bgiframe.min.js";s:4:"a868";s:66:"res/bt-0.9.5-rc1/other_libs/bgiframe_2.1.1/jquery.bgiframe.pack.js";s:4:"7b5a";s:58:"res/bt-0.9.5-rc1/other_libs/bgiframe_2.1.1/docs/index.html";s:4:"6529";s:58:"res/bt-0.9.5-rc1/other_libs/bgiframe_2.1.1/test/index.html";s:4:"2c0e";s:14:"doc/manual.sxw";s:4:"48c0";s:19:"doc/wizard_form.dat";s:4:"e21a";s:20:"doc/wizard_form.html";s:4:"5f71";s:49:"hooks/class.tx_newscalendar_additionalMarkers.php";s:4:"ab23";}',
	'suggests' => array(
	),
);

?>