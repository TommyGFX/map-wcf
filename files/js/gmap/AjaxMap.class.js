/**
 * allow to refresh map from ajax context
 *
 * @author      Torben Brodt <easy-coding.de>
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/Gmap
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
var AjaxMap = Class.create(Map3, {
	initialize: function($super, url, divID, switchable) {
		$super(divID, switchable);
		
		google.maps.event.addListener(this.gmap, 'bounds_changed', function(map) {
			return function() {
				map.currentBounds = map.gmap.getBounds();
			};
		}(this));

		this.mapInitialized = false;
		this.url = url;
		this.requestCounter = 0;
		this.events = [];
		this.markers =  [];
		this.boundsUsed = null;
		this.zoomUsed = null;
		this.currentBounds = null;
	},
	
	registerEvent: function (callback) {
		this.events.push(callback);
	},

	fireClickEvent: function (marker) {
		var id = ++this.requestCounter;

		var infoWindow = new google.maps.InfoWindow({ content: '<div style="overflow:auto;width:217px;height:70px;" id="info-' + id + '">' +
			'<img src="' + RELATIVE_WCF_DIR + 'icon/gmap/ajax-loader.gif" alt="" />' + '</div>' });
		infoWindow.open(this.gmap, marker);
		
		var url = this.url;
		url += '&zoom=' + this.zoomUsed;
		url += '&bounds=' + this.boundsUsed;
		url += '&action=pick';
		url += '&idx=' + marker.idx;
		var ajaxRequest = new AjaxRequest();
		ajaxRequest.openGet(url + SID_ARG_2ND, function (map, id) {
			return function () {
				if (ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
					var avatar, dom = document.getElementById('info-' + id);
					if (!dom) {
						return;
					}
					var data = eval('(' + ajaxRequest.xmlHttpRequest.responseText + ')');
					var html = '<ul class="dataList">';
					for (var i = 0; i < data.length; i++) {
						avatar = data[i][2] ? data[i][2] : RELATIVE_WCF_DIR + 'images/avatars/avatar-default.png';
						html += '<li class="container-' + (i % 2 === 0 ? 1 : 0) + '">' + '<div class="containerIcon">' + '<a href="index.php?page=User&amp;userID=' + data[i][0] + SID_ARG_2ND + '">' + '<img style="width:24px;height:24px" src="' + avatar + '" alt="" />' + '</a>' + '</div>' + '<div class="containerContent">' + '<a href="index.php?page=User&amp;userID=' + data[i][0] + SID_ARG_2ND + '">' + data[i][1] + '</a>' + '</div>' + '</li>';
					}
					html += '</ul>';
					dom.innerHTML = html;
				};
			};
		}(this, id));
	},
	getBounds: function () {
		var bounds = this.currentBounds;
		if(!bounds) {
			return null;
		}
		var a = bounds.getSouthWest();
		var b = bounds.getNorthEast();
		a.y -= Math.abs(a.y - b.y) * 0.3;
		b.y += Math.abs(a.y - b.y) * 0.3;
		a.x -= Math.abs(a.x - b.x) * 0.2;
		b.x += Math.abs(a.x - b.x) * 0.2;
		bounds.extend(new google.maps.LatLng(a.y, a.x));
		bounds.extend(new google.maps.LatLng(b.y, b.x));
		return bounds;
	},
	needsUpdate: function () {
		if (!this.mapInitialized) {
			return true;
		}
		if (this.gmap.getZoom() != this.zoomUsed) {
			return true;
		}
		return this.getBounds() === null || !this.boundsUsed.contains(this.currentBounds);
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
		var bounds, zoom, url = this.url;
		if (this.mapInitialized) {
			bounds = this.getBounds();
			zoom = this.gmap.getZoom();
			url += '&zoom=' + zoom;
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
							if (data[i].count && false) {
								marker = new ClusterMarker(
									map.gmap,
									new google.maps.LatLng(data[i].lat, data[i].lon), 
									data[i].count, 
									RELATIVE_WCF_DIR + 'icon/gmap/'
								);
							} else {
								marker = new google.maps.Marker({
									clickable: true,
									map: map.gmap,
									position: coordinates
								});
							}
							marker.idx = i;
							google.maps.event.addListener(marker, "click", function (map, marker) {
								return function () {
									map.fireClickEvent(marker);
								};
							}(map, marker));
						
							// increase marker count
							map.coordinates[map.markerCount] = [data[i].lat, data[i].lon];
							map.markers.push(marker);
							map.markerCount++;
						}
					} 
					
					// first load
					else {
						map.mapInitialized = true;
						if(data.length && data[0] && data[0].lat) {
							map.coordinates[map.markerCount] = [data[0].lat, data[0].lon];
							map.markerCount++;
							map.showMap();
							// map.setBounds();
							// map.clearMarkers();

							map.update();
							map.runEvents();
						} else {
							map.showMap();
						}
					}
				}
			};
		}(this, bounds, zoom));
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
