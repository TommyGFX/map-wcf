<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;key={MAP_API}&amp;oe={CHARSET}"></script>
<script type="text/javascript">
//<![CDATA[
var val = document.getElementById('map_api').value;
if(val == '' || val == 'Api-Key') {
	var hideAll = document.getElementById('map_apiDiv').parentNode.parentNode.getElementsByTagName('div');
	for(var i=0; i<hideAll.length; i++) {
		if(hideAll[i].id && hideAll[i].id != 'map_apiDiv') {
			hideAll[i].style.display = 'none';
		}
	}

} else {
	var root = document.getElementById('map_bigmap_centerDiv').parentNode;
	
	var ctl_navi = new GSmallMapControl();
	var ctl_type = new GMapTypeControl();
	var ctl_scal = new GScaleControl();
	var ctl_over = new GOverviewMapControl();

	var div = document.createElement('div');
	div.setAttribute('id', 'map');
	div.style.width = '100%';
	div.style.height = '500px';
	root.insertBefore(div, document.getElementById('map_bigmap_centerDiv'));

	var map = new GMap2(div);
	var lat = parseFloat(document.getElementById('map_bigmap_center').value.split(',')[0]);
	var lng = parseFloat(document.getElementById('map_bigmap_center').value.split(',')[1]);
	var zoom = parseInt(document.getElementById('map_bigmap_zoom').value);
	map.setCenter(new GLatLng(lat, lng), zoom);

	function gShowHide(value, trueFalse) {
		switch(value) {
			case 'map_bigmap_ctl_navi':
				if(trueFalse)map.addControl(ctl_navi);
				else map.removeControl(ctl_navi);
			break;
			case 'map_bigmap_ctl_type':
				if(trueFalse)map.addControl(ctl_type);
				else map.removeControl(ctl_type);
			break;
			case 'map_bigmap_ctl_scal':
				if(trueFalse)map.addControl(ctl_scal);
				else map.removeControl(ctl_scal);
			break;
			case 'map_bigmap_ctl_over':
				if(trueFalse)map.addControl(ctl_over);
				else map.removeControl(ctl_over);
			break;
			case 'map_bigmap_ctl_mousewheelzoom':
				if(trueFalse)map.enableScrollWheelZoom();
				else map.disableScrollWheelZoom();
			break;
		}
	}

	var SearchHide = /^map_bigmap_(center|zoom|type)(Div|HelpMessage)$/;
	var SearchClick = /^map_bigmap_ctl(.+)Div$/;
	var elems = root.getElementsByTagName('div');
	for(var i=0; i<elems.length; i++) {
		if(elems[i].id) {
			if(SearchHide.test(elems[i].id)) {
				elems[i].style.display = 'none';
			}
			if(SearchClick.test(elems[i].id)) {
				elems[i].getElementsByTagName('input')[0].onchange = function() {
					gShowHide(this.id, this.checked);
				};
			}
		}
	}

	GEvent.addListener(map, "moveend", function() {
		var center = map.getCenter();
		var lat = center.lat();
		var lng = center.lng();
		document.getElementById('map_bigmap_centerDiv').getElementsByTagName('input')[0].value = lat+","+lng;
	});

	GEvent.addListener(map, "zoomend", function() {
		var zoomindex = map.getZoom()-1;
		document.getElementById('map_bigmap_zoom').selectedIndex = zoomindex;
	});

	GEvent.addListener(map, "maptypechanged", function() {
		var maptypeindex = 0;
		switch(map.getCurrentMapType().getUrlArg()) {
			case 'm':
				maptypeindex = 2;
			break;
			case 's':
				maptypeindex = 0;
			break;
			case 'h':
				maptypeindex = 1;
			break;
		}
		document.getElementById('map_bigmap_type').selectedIndex = maptypeindex;
	});

	if(document.getElementById('map_bigmap_ctl_mousewheelzoom').checked) map.enableScrollWheelZoom();
	if(document.getElementById('map_bigmap_ctl_navi').checked) map.addControl(ctl_navi);
	if(document.getElementById('map_bigmap_ctl_type').checked) map.addControl(ctl_type);
	if(document.getElementById('map_bigmap_ctl_scal').checked) map.addControl(ctl_scal);
	if(document.getElementById('map_bigmap_ctl_over').checked) map.addControl(ctl_over);

	switch(document.getElementById('map_bigmap_type').value) {
		case 'm':map.setMapType(G_NORMAL_MAP);
		break;
		case 'h':map.setMapType(G_HYBRID_MAP);
		break;
		case 's':map.setMapType(G_SATELLITE_MAP);
		break;
	}
}
//]]>
</script>
