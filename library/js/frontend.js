(function($, doucment, undefined){


	jQuery(document).ready(function($){
		for (var i =  PNS.jsTarget.length - 1; i >= 0; i--) {
			var swappy1 = $( PNS.jsTarget[i] );

			if(!swappy1.is('a')) {
				var a_tag = $('<a>').attr( "href", "tel:" + PNS.phoneNumbers[i] ).text(PNS.phoneNumbers[i]);
				swappy1.html(a_tag);
			} else {
				swappy1.attr( "href", "tel:" + PNS.phoneNumbers[i] ).text(PNS.phoneNumbers[i]);
			}
		};
	});

})(jQuery, document);
