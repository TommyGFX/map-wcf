/**
 * allow to refresh map from ajax context
 *
 * @author      Torben Brodt <easy-coding.de>
 * @url		https://github.com/torbenbrodt/map-wcf
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
var AjaxMap = Class.create(Map3, {
	initialize: function($super, url, divID, lookupClientLocation) {
		$super(divID, lookupClientLocation);
		
		google.maps.event.addListener(this.gmap, 'bounds_changed', function(map) {
			return function() {
				map.zoomDisplayed = map.gmap.getZoom();
				map.boundsDisplayed = map.gmap.getBounds();
			};
		}(this));

		this.mapInitialized = false;
		this.url = url;
		this.requestCounter = 0;
		this.events = [];
		this.markers =  [];
		this.boundsDisplayed = null;
		this.boundsUsed = null;
		this.zoomUsed = null;
		this.zoomDisplayed = null;
		this.infoWindow = null;
	},
	
	registerEvent: function (callback) {
		this.events.push(callback);
	},

	fireClickEvent: function (marker) {
		var id = ++this.requestCounter;

		if(this.infoWindow === null) {
			this.infoWindow = new google.maps.InfoWindow({
				content: '<div style="overflow:auto;width:217px;height:70px;" id="' + this.divID + 'infoWindow">' +
					'<img src="' + RELATIVE_WCF_DIR + 'icon/gmap/ajax-loader.gif" alt="" />' + 
					'</div>'
			});
		}

		var c = this.coordinates[marker.getLatLng()];
		this.infoWindow.setPosition(new google.maps.LatLng(c[0], c[1]));
		this.infoWindow.open(this.gmap);
		
		var url = this.url;
		url += '&zoom=' + this.zoomUsed;
		url += '&bounds=' + this.boundsUsed;
		url += '&action=pick';
		url += '&idx=' + marker.idx;
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet(url + SID_ARG_2ND, function (map, id) {
			return function () {
				if (ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
					var avatar, dom = document.getElementById(map.divID + 'infoWindow');
					if (!dom) {
						return;
					}
					var data = eval('(' + ajaxRequest.xmlHttpRequest.responseText + ')');
					var html = '<ul class="dataList">';
					for (var i = 0; i < data.length; i++) {
						avatar = data[i][2] ? data[i][2] : RELATIVE_WCF_DIR + 'images/avatars/avatar-default.png';
						html += '<li class="container-' + (i % 2 === 0 ? 1 : 2) + '">' + '<div class="containerIcon">' + '<a href="index.php?page=User&amp;userID=' + data[i][0] + SID_ARG_2ND + '">' + '<img style="width:24px;height:24px" src="' + avatar + '" alt="" />' + '</a>' + '</div>' + '<div class="containerContent">' + '<a href="index.php?page=User&amp;userID=' + data[i][0] + SID_ARG_2ND + '">' + data[i][1] + '</a>' + '</div>' + '</li>';
					}
					html += '</ul>';
					dom.innerHTML = html;
				};
			};
		}(this, id));
	},
	expand: function (latLng) {
		var a = latLng.getSouthWest();
		var b = latLng.getNorthEast();
		var a_lat = a.lat() - ( Math.abs(a.lat() - b.lat()) * 0.3 );
		var b_lat = b.lat() + ( Math.abs(a.lat() - b.lat()) * 0.3 );
		var a_lng = a.lng() - ( Math.abs(a.lng() - b.lng()) * 0.2 );
		var b_lng = b.lng() + ( Math.abs(a.lng() - b.lng()) * 0.2 );
		latLng.extend(new google.maps.LatLng(a_lat, a_lng));
		latLng.extend(new google.maps.LatLng(b_lat, b_lng));
	},
	needsUpdate: function () {
		if (!this.mapInitialized) {
			return true;
		}
		if (this.gmap.getZoom() != this.zoomUsed) {
			return true;
		}
		var bd = this.boundsDisplayed;
		if(!bd) {
			return true;
		}
		bd = [bd.getSouthWest(), bd.getNorthEast()];
		for(var i=0; i<bd.length; i++) {
			if(this.boundsUsed.contains(bd[i]) == false) {
				return true;
			}
		}
		return false;
	},
	clearMarkers: function() {
	    for(var i=0; i < this.markers.length; i++){
		this.markers[i].setMap(null);
	    }
	    this.markers = [];
	},
	update: function () {
		if (!this.needsUpdate()) {
			return;
		}
		var url = this.url;

		// load with extra percents
		bounds = this.boundsDisplayed;
		if(bounds) {
			this.expand(bounds);
		}

		if (this.mapInitialized) {
			url += '&zoom=' + this.zoomDisplayed;
			url += '&bounds=' + bounds;
			url += '&action=update';
		} else {
			url += '&action=initialize';
		}
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet(url + SID_ARG_2ND, function (map, bounds, zoom) {
			return function () {
				if (ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
					map.boundsUsed = bounds;
					map.zoomUsed = zoom;
					var data = eval('(' + ajaxRequest.xmlHttpRequest.responseText + ')');
					var coordinates, overlay;
					
					// update map
					if (map.mapInitialized) {
						map.clearMarkers();
						map.markerCount = 0;

						for (var i = 0; i < data.length; i++) {

							coordinates = new google.maps.LatLng(data[i].lat, data[i].lon);
							if (data[i].count) {
								marker = new ClusterMarker(
									map,
									coordinates,
									data[i].count,
									RELATIVE_WCF_DIR + 'icon/gmap/'
								);
							} else {
								marker = new google.maps.Marker({
									clickable: true,
									map: map.gmap,
									position: coordinates
								});
								google.maps.event.addListener(marker, "click", function (map, marker) {
									return function () {
										map.fireClickEvent(marker);
									};
								}(map, marker));
							}
							marker.getLatLng = function() {
								return this.idx;
							};
							marker.idx = i;

							// increase marker count
							map.markerCount = i;
							map.coordinates[map.markerCount] = [data[i].lat, data[i].lon];
							map.markers[map.markerCount] = marker;
						}
					} 
					
					// first load
					else {
						map.mapInitialized = true;
						if(data.length && data[0] && data[0].lat) {
							map.coordinates[map.markerCount] = [data[0].lat, data[0].lon];
							map.markerCount++;
							map.showMap();

							map.update();
							map.runEvents();
						} else {
							map.showMap();
						}
					}
				}
			};
		}(this, bounds, this.zoomDisplayed));
	},
	runEvents: function () {
		google.maps.event.addListener(this.gmap, "idle", function (map) {
			return function () {
				map.update();
			};
		}(this));
		for (var i = 0; i < this.events.length; i++) {
			this.events[i]();
		}
	}
});
