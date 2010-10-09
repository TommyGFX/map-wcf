{include file='gmapConstants'}
<script type="text/javascript">
//<![CDATA[
GMAP_ENABLE_STREETVIEW = 1; 
GMAP_MAP_CONTROL = 'off';
//]]>
</script>
<script src="{@RELATIVE_WCF_DIR}js/gmap/Map.class.js" type="text/javascript"></script>
<script src="{@RELATIVE_WCF_DIR}js/gmap/BBCodeMap.class.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
if (GMAP_API_KEY != '') {
	document.write('<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;hl={@$this->language->getLanguageCode()}&amp;key=' + GMAP_API_KEY + '&amp;oe={CHARSET}" type="text/javascript"><\/script>');
	document.write('<script src="{@RELATIVE_WCF_DIR}js/gmap/StreetViewControl.class.js" type="text/javascript"><\/script>');
	onloadEvents.push(function() {
		var gmap = new BBCodeMap();
		gmap.registerEvent(function(map) {
			return function() {
				if(GMAP_ENABLE_STREETVIEW) gmap.gmap.addControl(new StreetViewControl());
			}
		}(gmap));
		gmap.write();

		return function() {
			if (GBrowserIsCompatible()) {
				var marker, coordinates;
				var data = {@$bbcodemap_data|json_encode};
				for(var i=0; i<data.length; i++) {
					coordinates = new GLatLng(data[i].lat, data[i].lon);
					if(i == 0) {
						gmap.setCoordinates(coordinates);
						gmap.gmap.clearOverlays();
						gmap.runEvents();
					}
					
					marker = new GMarker(coordinates);
					gmap.gmap.addOverlay(marker);
				}
			}
		};
	}());
}
//]]>
</script>
