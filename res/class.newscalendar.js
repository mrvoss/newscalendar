/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var newscalendar = {}


newscalendar.tipSetup = function(
	width,
	backgroundColor,
	borderColor,
	borderWidth,
	radius,
	padding,
	spikeLength,
	spikeGirth,
	shadow,
	shadowBlur,
	shadowOffsetX,
	shadowOffsetY,
	positions,
	fadeSpeed) {

	newscalendar.tip = {

		width : width,
		backgroundColor : backgroundColor,
		borderColor : borderColor,
		borderWidth : borderWidth,
		radius : radius,
		padding : padding,
		spikeLength : spikeLength,
		spikeGirth : spikeGirth,
		shadow: shadow,
		shadowBlur: shadowBlur,
		shadowOffsetX: shadowOffsetX,
		shadowOffsetY: shadowOffsetY,
		positions: positions,
		fadeSpeed : fadeSpeed
	}

}

newscalendar.addToolTipSelectorClass = function( element, className ) {
    jQuery( element ).addClass( className );
}

newscalendar.removeToolTipSelectorClass = function( element, className ) {
    jQuery( element ).removeClass( className );
}

newscalendar.processToolTip = function( toolTipID ) {

	try {

		jQuery( '#idMenu' + toolTipID ).bt({

			    shadow: newscalendar.tip.shadow,
			    shadowBlur: newscalendar.tip.shadowBlur,
			    shadowOffsetX: newscalendar.tip.shadowOffsetX,
			    shadowOffsetY: newscalendar.tip.shadowOffsetY,
				positions: newscalendar.tip.positions,
				cssClass: 'newscalendar-tip newscalendar-tip-id-' + toolTipID ,
				trigger : 'none',
				contentSelector : 'jQuery( "#toolTipIdMenu' + toolTipID + '").html()',
				padding: newscalendar.tip.padding,
				width: newscalendar.tip.width,
				spikeLength: newscalendar.tip.spikeLength,
				spikeGirth: newscalendar.tip.spikeGirth,
				cornerRadius: newscalendar.tip.radius,
				fill: newscalendar.tip.backgroundColor,
				strokeWidth: newscalendar.tip.borderWidth,
				strokeStyle: newscalendar.tip.borderColor,
				showTip: function(box){
					jQuery(box).fadeIn(newscalendar.tip.fadeSpeed);
				},
				hideTip: function(box, callback){
					callback();
				},
				shrinkToFit: true,
				hoverIntentOpts: {
					interval: 0,
					timeout: 0
				}

		});

		jQuery( '#idMenu' + toolTipID ).mouseover( function( event ) {

			event.preventDefault();
			var currentSelectStarter = this;
			newscalendar.addToolTipSelectorClass( currentSelectStarter, 'newscalendar-tip-selector' );


			jQuery( '#idMenu' + toolTipID ).btOn();

			jQuery( '.newscalendar-tip-id-' + toolTipID ).bind( 'mouseleave', function( event ) {

				event.preventDefault();
				jQuery( '#idMenu' + toolTipID ).btOff();
				newscalendar.removeToolTipSelectorClass( currentSelectStarter, 'newscalendar-tip-selector' );

			});

			jQuery( '#idMenu' + toolTipID ).mouseout( function( event ) {

				event.preventDefault();

				var checkTo = 'not_defined';
				if ( typeof event.toElement !== "undefined" ) {
				    checkTo = event.toElement.tagName ;
				}  else if ( typeof event.relatedTarget !== "undefined" ) {
				    checkTo = event.relatedTarget.localName;
				}

				try {
				    if ( checkTo !== 'canvas' && checkTo !== 'CANVAS' &&  checkTo !== 'shape' ) {

					jQuery( '#idMenu' + toolTipID ).btOff();
					newscalendar.removeToolTipSelectorClass( currentSelectStarter, 'newscalendar-tip-selector' );

				    }
				} catch(e) {}

			} );


		} );

	} catch( e ) {}
}