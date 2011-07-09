{include file='gmapConstants'}
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&amp;language={@$this->language->getLanguageCode()}"></script>
<script src="{@RELATIVE_WCF_DIR}js/gmap/Map3.class.js" type="text/javascript"></script>
<script src="{@RELATIVE_WCF_DIR}js/gmap/BBCodeMap.class.js" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
onloadEvents.push(function() {
	var gmap = new BBCodeMap();

	// write div layer with unique id
	gmap.write();

	return function() {
		var data = {@$bbcodemap_data|json_encode};

		var coordinates = [];
		for(var i=0; i<data.length; i++) {
			coordinates.push({
				latitude: data[i].lat,
				longitude: data[i].lon
			});
		}

		if(coordinates.length) {
			gmap.lazyInit();
			gmap.loadMarkers(coordinates);
			gmap.showMap();
			{if $bbcodemap_zoom}gmap.gmap.setZoom({$bbcodemap_zoom});{/if}
		}
	};
}());
//]]>
</script>
