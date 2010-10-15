/**
 * allow to refresh map from ajax context
 *
 * @author      Torben Brodt <easy-coding.de>
 * @url		http://trac.easy-coding.de/trac/wcf/wiki/Gmap
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
function AjaxMap(url, divID, switchable) {
	this.url = url;
	this.requestCounter = 0;
	this.events = [];
	this.boundsUsed = null;
	this.zoomUsed = null;
	this.constructor(divID, switchable);
	this.registerEvent = function (callback) {
		this.events.push(callback);
	};
	this.fireClickEvent = function (marker) {
		var id = ++this.requestCounter;
		marker.openInfoWindowHtml('<div style="overflow:auto;width:217px;height:70px;" id="info-' + id + '">' +
			'<img src="' + RELATIVE_WCF_DIR + 'icon/gmap/ajax-loader.gif" alt="" />' + '</div>');
		var url = this.url;
		url += '&zoom=' + this.zoomUsed;
		url += '&bounds=' + this.boundsUsed;
		url += '&action=pick';
		url += '&lon=' + marker.getLatLng().x;
		url += '&lat=' + marker.getLatLng().y;
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
	};
	this.getBounds = function () {
		var bounds = this.gmap.getBounds();
		var a = bounds.getSouthWest();
		var b = bounds.getNorthEast();
		a.y -= Math.abs(a.y - b.y) * 0.3;
		b.y += Math.abs(a.y - b.y) * 0.3;
		a.x -= Math.abs(a.x - b.x) * 0.2;
		b.x += Math.abs(a.x - b.x) * 0.2;
		bounds.extend(new GLatLng(a.y, a.x));
		bounds.extend(new GLatLng(b.y, b.x));
		return bounds;
	};
	this.needsUpdate = function () {
		if (!this.mapInitialized) {
			return true;
		}
		if (this.gmap.getZoom() != this.zoomUsed) {
			return true;
		}
		return !this.boundsUsed.containsBounds(this.gmap.getBounds());
	};
	this.update = function () {
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
						map.gmap.clearOverlays();
						for (var i = 0; i < data.length; i++) {
							coordinates = new GLatLng(data[i].lat, data[i].lon);
							if (data[i].count) {
								marker = new ClusterMarker(new GLatLng(data[i].lat, data[i].lon), data[i].count, RELATIVE_WCF_DIR + 'icon/gmap/');
							} else {
								marker = new GMarker(coordinates);
							}
							GEvent.addListener(marker, "click", function (map, marker) {
								return function () {
									map.fireClickEvent(marker);
								};
							}(map, marker));
							map.gmap.addOverlay(marker);
						}
					} 
					
					// first load
					else {
						coordinates = new GLatLng(data[0].lat, data[0].lon);
						map.setCoordinates(coordinates);
						map.gmap.clearOverlays();
						map.update();
						map.runEvents();
					}
				}
			};
		}(this, bounds, zoom));
	};
	this.runEvents = function () {
		GEvent.addListener(this.gmap, "moveend", function (map) {
			return function () {
				map.update();
			};
		}(this));
		for (var i = 0; i < this.events.length; i++) {
			this.events[i]();
		}
	};
};
AjaxMap.prototype = new Map();
