/******************************************************************************\
*  StreetViewControl.js		                 by Mike Reumer, Torben Brodt *
*  A Google Maps API Extension  StreetView Control                            *
*  Extra button for 3D-large drag/zoom button control to show streetview like *
*  maps.google.com                                                            *
*  GPL license http://www.gnu.org/licenses/gpl.html                           *
*  Version: 1.1 Date:4/9/2010                                                 *
*  deobfuscated by Torben Brodt at http://www.easy-coding.de                  *
\******************************************************************************/
function SVControl() {
	var map;
	var img;
	var mouseover;
	var dragging;
	var hasStreetviewData;
	var marker;
}
SVControl.prototype = new GControl();
SVControl.prototype.initialize = function (map) {
	this.map = map;
	this.mouseover = false;
	this.dragging = false;
	this.streetviewclicked = false;
	this.hasStreetviewData = false;
	this.browserflashbug = ((navigator.vendor) && navigator.vendor.indexOf("Apple") != -1) 
		|| ((navigator.userAgent) && navigator.userAgent.indexOf("Chrome") != -1);
	this.width = this.map.getContainer().style.width;
	this.height = this.map.getContainer().style.height;
	this.panoramacontainer = document.createElement("div");
	this.map.getContainer().parentNode.insertBefore(
		this.panoramacontainer, 
		this.map.getContainer().nextSibling
	);
	this.streetview = new GStreetviewOverlay();
	this.map.addOverlay(this.streetview);
	this.streetview.hide();
	this.streetview.SVControl = this;
	GEvent.addListener(this.streetview, "changed", this.changedstreetview);
	this.streetviewclient = new GStreetviewClient();
	this.map.addControl(new GLargeMapControl3D(true));
	this.container = document.createElement("div");
	this.container.style.cssText = "overflow: hidden; width: 25px; height: 40px; position: absolute;";
	this.svbutton = document.createElement("div");
	this.svbutton.style.cssText = "overflow: hidden; width: 25px; height: 40px; z-index: 10001;";
	this.svbutton.SVControl = this;
	this.container.appendChild(this.svbutton);
	this.img = document.createElement("img");
	this.img.setAttribute("src", "http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_003.png");
	this.img.style.cssText = "border: 0px none ; margin: 0px; padding: 0px; "+
		"position: absolute; left: -62px; top: -40px; width: 147px; height: 935px;";
	this.svbutton.appendChild(this.img);
	map.getContainer().appendChild(this.container);
	this.dragbutton = new GDraggableObject(this.container, {
		container: map.getContainer()
	});
	this.dragbutton.SVControl = this;
	GEvent.addDomListener(this.svbutton, "mouseover", this.funcmouseover);
	GEvent.addDomListener(this.svbutton, "mouseout", this.funcmouseout);
	GEvent.addDomListener(this.dragbutton, "dragstart", this.funcdragstart);
	GEvent.addDomListener(this.dragbutton, "dragend", this.funcdragend);
	this.panorama = new GStreetviewPanorama(this.panoramacontainer);
	this.panorama.SVControl = this;
	GEvent.addDomListener(this.panorama, "error", this.hidestreetview);
	return this.container;
};
SVControl.prototype.getDefaultPosition = function () {
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(25, 66));
};
SVControl.prototype.funcmouseover = function () {
	if (!this.SVControl.dragging) {
		if (this.SVControl.hasStreetviewData) {
			this.SVControl.mouseover = true;
			this.SVControl.img.style.left = "-62px";
			this.SVControl.img.style.top = "-320px";
			this.SVControl.streetview.show();
		}
	}
};
SVControl.prototype.funcmouseout = function () {
	if (!this.SVControl.dragging) {
		if (this.SVControl.mouseover) {
			this.SVControl.mouseover = false;
			this.SVControl.img.style.left = "-62px";
			this.SVControl.img.style.top = "-40px";
			this.SVControl.streetview.hide();
		}
	}
};
SVControl.prototype.funcdragstart = function () {
	this.SVControl.dragging = true;
	this.SVControl.container.style.width = "30px";
	this.SVControl.svbutton.style.width = "30px";
	this.SVControl.img.style.left = "-52px";
	this.SVControl.img.style.top = "-800px";
};
SVControl.prototype.funcdragend = function () {
	function callback(func, opts) {
		var cb = function () {
			var args = opts.args ? opts.args : [];
			var bind = opts.bind ? opts.bind : this;
			var fargs = opts.supressArgs === true ? [] : toArray(arguments);
			func.apply(bind, fargs.concat(args));
		};
		return cb;
	}

	function toArray(arrayLike) {
		var arr = [];
		for (var i = 0; i < arrayLike.length; i++) {
			arr.push(arrayLike[i]);
		}
		return arr;
	}
	if (this.SVControl.dragging) {
		this.SVControl.dragging = false;
		this.SVControl.point = this.SVControl.map.fromContainerPixelToLatLng(
			new GPoint(this.left + (25 / 2), this.top + (66 / 2))
		);
		this.SVControl.streetviewclient.getNearestPanoramaLatLng(
			this.SVControl.point, 
			callback(this.SVControl.showstreetview, {
				bind: this.SVControl,
				args: [],
				suppressArgs: true
			}
		));
		this.SVControl.container.style.width = "25px";
		this.SVControl.svbutton.style.width = "25px";
		this.SVControl.img.style.left = "-62px";
		this.SVControl.img.style.top = "-320px";
		this.moveTo(new GPoint(25, 66));
	}
};
SVControl.prototype.funcdrag = function () {
	if (this.SVControl.dragging) {}
};
SVControl.prototype.showstreetview = function (point) {
	if (point) {
		var posx;
		this.point = point;
		this.panorama.remove();
		this.panorama.setLocationAndPOV(this.point, null);
		this.width = this.map.getContainer().style.width;
		this.clientWidth = this.map.getContainer().clientWidth;
		this.height = this.map.getContainer().style.height;
		this.map.getContainer().style.width = "0px";
		this.map.getContainer().style.height = "0px";
		this.map.getContainer().style.overflow = "hidden";
		this.map.checkResize();
		this.map.removeOverlay(this.streetview);
		if (this.browserflashbug) {
			this.panoramacontainer.style.width = (parseInt(this.clientWidth) - 18) + "px";
		}
		else {
			this.panoramacontainer.style.width = this.width;
		}
		this.panoramacontainer.style.height = this.height;
		this.panorama.checkResize();
		this.closecontainer = document.createElement("div");
		if (this.browserflashbug) {
			posx = this.panoramacontainer.clientWidth;
		}
		else {
			posx = parseInt(this.panoramacontainer.clientWidth) - 21 - 16 - 2;
		}
		var posy = -this.panoramacontainer.clientHeight + 4;
		this.closecontainer.style.cssText = "float:left; overflow: hidden; width: 16px; height: 16px; "+
			"position: relative; cursor: pointer; left: " + posx + "px; top: " + posy + "px;";
		var img = document.createElement("img");
		img.setAttribute("src", "http://maps.gstatic.com/mapfiles/cb/close-cross_v2.png");
		img.style.cssText = "border: 0px none ; margin: 0px; padding: 0px; position: absolute; "+
			"left: -16px; top: 0px;  width: 32px; height: 16px;";
		this.closecontainer.appendChild(img);
		this.closecontainer.SVControl = this;
		this.panoramacontainer.parentNode.appendChild(this.closecontainer);
		this.closebuttonevent = GEvent.addDomListener(this.closecontainer, "click", this.hidestreetview);
	}
};
SVControl.prototype.hidestreetview = function () {
	this.SVControl.panoramacontainer.style.width = "0px";
	this.SVControl.panoramacontainer.style.height = "0px";
	this.SVControl.panoramacontainer.style.overflow = "hidden";
	this.SVControl.panorama.checkResize();
	this.SVControl.panorama.hide();
	this.SVControl.map.getContainer().style.width = this.SVControl.width;
	this.SVControl.map.getContainer().style.height = this.SVControl.height;
	this.SVControl.map.checkResize();
	this.SVControl.map.addOverlay(this.SVControl.streetview);
	this.SVControl.panoramacontainer.parentNode.removeChild(this.SVControl.closecontainer);
	GEvent.removeListener(this.SVControl.closebuttonevent);
};
SVControl.prototype.changedstreetview = function (hasStreetviewData) {
	this.SVControl.hasStreetviewData = hasStreetviewData;
	if (hasStreetviewData) {
		if (this.SVControl.mouseover) {
			this.SVControl.img.style.left = "-62px";
			this.SVControl.img.style.top = "-320px";
		} else {
			this.SVControl.img.style.left = "-62px";
			this.SVControl.img.style.top = "-40px";
		}
	} else {
		this.SVControl.img.style.left = "-102px";
		this.SVControl.img.style.top = "-845px";
	}
};

function StreetViewControl() {
	var container;
	var container2;
}
StreetViewControl.prototype = new GControl();
StreetViewControl.prototype.initialize = function (map) {
	this.container = document.createElement("div");
	this.container.style.cssText = "overflow: hidden; width: 20px; height: 40px; position: absolute;";
	var svbutton = document.createElement("div");
	svbutton.style.cssText = "overflow: hidden; width: 20px; height: 40px; z-index: 10001;";
	this.container.appendChild(svbutton);
	var img = document.createElement("img");
	img.setAttribute("src", "http://maps.gstatic.com/mapfiles/cb/mod_cb_scout/cb_scout_sprite_003.png");
	img.style.cssText = "border: 0px none ; margin: 0px; padding: 0px; position: absolute; "+
		"left: -102px; top: -845px; width: 147px; height: 935px;";
	svbutton.appendChild(img);
	map.getContainer().appendChild(this.container);
	this.container2 = new SVControl(map);
	map.addControl(this.container2);
	return this.container;
};
StreetViewControl.prototype.getDefaultPosition = function () {
	return new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(25, 66));
};
