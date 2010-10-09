/**
 * allow to refresh map from ajax context
 *
 * @author      Torben Brodt <easy-coding.de>
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/Gmap
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
var AjaxMap = function(url, divID, switchable) {
	this.url = url;
	this.constructor(divID, switchable);
	this.events = [];
	
	this.registerEvent = function(callback) {
		this.events.push(callback);
	};
	
	/**
	 * 
	 */
	this.fireClickEvent = function(marker) {
		marker.openInfoWindowHtml(marker.getLatLng() + "\nzoom: " + this.gmap.getZoom());
		// start ajax request for ClusterMarker here
	};

	this.update = function() {
		url = this.url;
		
		if(this.mapInitialized) {
			url += '&zoom='+this.gmap.getZoom();
			url += '&bounds='+this.gmap.getBounds();
			url += '&initialized=1';
		}

		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet(url + SID_ARG_2ND, function(map) {
			return function() {
				if(ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
					var data = eval('(' + ajaxRequest.xmlHttpRequest.responseText + ')');
					var coordinates, overlay;

					if(map.mapInitialized) {
						map.gmap.clearOverlays();
						for(var i in data) {
							coordinates = new GLatLng(data[i].lat, data[i].lon);

							if(data[i].count) {
								marker = new ClusterMarker(
									new GLatLng(data[i].lat, data[i].lon),
									data[i].count,
									RELATIVE_WCF_DIR + 'icon/gmap/'
								);
							} else {
								marker = new GMarker(coordinates);
							}

							GEvent.addListener(marker, "click", function(map, marker) {
								return function() {
									map.fireClickEvent(marker);
								};
							}(map, marker));
							map.gmap.addOverlay(marker);
						}
					} else {
						coordinates = new GLatLng(data[0].lat, data[0].lon);
						map.setCoordinates(coordinates);
						map.gmap.clearOverlays();
						
						map.update();
						map.runEvents();
					}
				}
			};
		}(this));
	};
	
	this.runEvents = function() {
		GEvent.addListener(this.gmap, "moveend", function(map) {
			return function() {
				map.update();
			}
		}(this));

		for(var i=0; i<this.events.length; i++) {
			this.events[i]();
		}
	};
};
AjaxMap.prototype = new Map();
