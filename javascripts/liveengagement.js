/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var settings = $.extend( {
	rowHeight			: 25,
});
var history = [];

/**
 * jQueryUI widget for Live visitors widget
 */
$(function() {
    var refreshLiveEngagementWidget = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if ($(element).parent().length == 0) {
            return;
        }
        var lastMinutes = $(element).find('.dynameter').attr('data-last-minutes') || 30;

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'LiveEngagement.getLiveEngagement',
            format: 'json',
            lastMinutes: lastMinutes
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
        	data.sort(function(a, b){
        	    return b.percentage - a.percentage;
        	});
        	$.each( data, function( index, value ){
              	var pc = value['percentage'];
        		pc = pc > 100 ? 100 : pc;
        		$('#LiveEngagementChart').find("div[id="+value['id']+"]").children('.percent').html(pc+'%');
        		var ww = $('#LiveEngagementChart').find("div[id="+value['id']+"]").width();
        		var len = parseInt(ww, 10) * parseInt(pc, 10) / 100;
        		$('#LiveEngagementChart').find("div[id="+value['id']+"]").children('.bar').animate({ 'width' : len+'px' }, 1500);
        		$('#LiveEngagementChart').find("div[id="+value['id']+"]").attr("index", index);

        	});
			//animation
			var vertical_offset = 0; // Beginning distance of rows from the table body in pixels
			for ( index = 0; index < data.length; index++) {
				$("#LiveEngagementChart").find("div[index="+index+"]").stop().delay(1 * index).animate({ top: vertical_offset}, 1000, 'swing').appendTo("#LiveEngagementChart");
				vertical_offset += settings['rowHeight'];
			}
            // schedule another request
            setTimeout(function () { refreshLiveEngagementWidget(element, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/LiveEngagement");
    exports.initSimpleRealtimeLiveEngagementWidget = function (refreshInterval) {
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'LiveEngagement.getLiveEngagement',
            format: 'json',
            lastMinutes: 30
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
        	data.sort(function(a, b){
        	    return b.percentage - a.percentage;
        	});
            $('#LiveEngagementChart').each(function() {
                // Set table height and width
    			$("#LiveEngagementChart").height((data.length*settings['rowHeight']));

    			for (j=0; j<data.length; j++){
                	$("#LiveEngagementChart").find("div[index="+j+"]").css({ top: (j*settings['rowHeight']) }).appendTo("#LiveEngagementChart");
                }
            });
        	$.each( data, function( index, value ){
               	var pc = value['percentage'];
        		pc = pc > 100 ? 100 : pc;
        		$('#LiveEngagementChart').find("div[index="+index+"]").attr("id", value['id']);
        		$('#LiveEngagementChart').find("div[index="+index+"]").children('.percent').html(pc+'%');
        		$('#LiveEngagementChart').find("div[index="+index+"]").children('.title').text(value['name']);
        		var ww = $('#LiveEngagementChart').find("div[index="+index+"]").width();
        		var len = parseInt(ww, 10) * parseInt(pc, 10) / 100;
        		$('#LiveEngagementChart').find("div[index="+index+"]").children('.bar').animate({ 'width' : len+'px' }, 1500);
        	});
            $('#LiveEngagementChart').each(function() {
    			var $this = $(this),
                   refreshAfterXSecs = refreshInterval;
                setTimeout(function() { refreshLiveEngagementWidget($this, refreshAfterXSecs ); }, refreshAfterXSecs * 1000);
            });
        });
        ajaxRequest.send(true);
     };
});

