/**
 * First Version Thanks to Максим Левицкий - Maxim Levicky
 * Need Multilanguage capability
 * 2009-09-12
 */
jQuery.noConflict();
jQuery(document).ready(function(){
		jQuery(".columYear").css({'cursor' : 'pointer'})
		jQuery(".columYear").bind("click",function(){
		var changer = date_changer();
		jQuery(".calendar-table").before('<div id="date_changer" style="position:absolute;width:'+jQuery(".calendar-table").width()+'" class="rcMenuContainer">'+changer+'</div>');
		jQuery("#date_changer .changer_ok").click(function(){
			change_date();
		});
		jQuery("#date_changer .changer_no").click(function(){
			jQuery("#date_changer").remove();
		});
	});
});
//calendar date_changer
function date_changer(){
	var today = new Date();
	var date_to = new Date("December 30, 2006 23:59:59");
	var month_array = Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Nov", "Oct", "Dez");
	var year = today.getFullYear();
	var year_to = date_to.getFullYear();
	
	var present_url = document.URL;
	var reg_year=/tx_ttnews(%5B||\[)calendarYear(%5D||\])\=(\d\d\d\d)&+/;
	var reg_month=/tx_ttnews(%5B||\[)calendarMonth(%5D||\])\=(\d||\d\d)&/;
	if (reg_year.test(present_url)){
		cal_year = reg_year.exec(present_url);
		cal_year = cal_year[3]
	}
	else cal_year = year;
	if (reg_month.test(present_url)){
		cal_month = reg_month.exec(present_url);
		cal_month = cal_month[3]
	}
	else cal_month = 1+today.getMonth();
	
	output = '<select class="date_changer month">';
	
	i=1
	for(var key in month_array){
		var val = month_array [key];
		
		if(i == cal_month) selected = "selected ";
		else selected = "";
		
		output += '<option '+selected+'value="'+i+'">'+val+'</option>';
		if (i == 12) break;
		i++
	}
	output += '</select>';
	output += '<select class="date_changer year">';
	while(year >= year_to){
		if(year == cal_year) selected = "selected ";
		else selected = "";
		output += '<option '+selected+'value="'+year+'">'+year+'</option>';
		year--;
	}
	output += '</select>';
	output += '<img title="Change" class="changer_ok" src="typo3conf/ext/newscalendar/res/accept.png" />';
	output += '<img title="Cancel" class="changer_no" src="typo3conf/ext/newscalendar/res/cancel.png" />';
	return output;
}
function change_date(){
	var month = jQuery("#date_changer .month option:selected").attr("value");
	var year = jQuery("#date_changer .year option:selected").text();
	var present_url = document.URL;
	
	var reg_year=/tx_ttnews(%5B||\[)calendarYear(%5D||\])\=\d\d\d\d&+/;
	var reg_month=/tx_ttnews(%5B||\[)calendarMonth(%5D||\])\=(\d||\d\d)&/;
	var reg_no_cache=/no_cache\=1(\&?)/;
	var reg_have_question=/\?/;
	
	present_url = present_url.replace(reg_year, "");
	present_url = present_url.replace(reg_month, "");
	present_url = present_url.replace(reg_no_cache, "");
	have_question = present_url.test(reg_have_question);
	
	if (have_question) question = "&";
	else question = "?";

	/* Attention to no_cache=1 on RealURL activation*/
	window.location=present_url+question+'tx_ttnews[calendarYear]='+year+'&tx_ttnews[calendarMonth]='+month+'&no_cache=1';
}
//calendar date_changer end