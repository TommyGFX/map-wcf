/**
 * G-Map
 *
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */

var gmap_admin = false; // administration enabled/disabled
var gmap_count_users, gmap_count_personal; // counters
var gmap_groups = 125; // distance from the top
var gmap_line = new Array(); // for drawing lines
var ajax_hidegroup = new Array();
var ajax_showusers = new Array();
var gmap_type_prior, gmap_type_current;

/**
 * saves a new marker
 * @param text -> text for the notice
 * @return -> returns false to skip link processing
 */
function gRequestAdd(text) {
	gNotice(text, -1);

	var myEventListener = GEvent.bind(map, "click", this, function(marker, point) {
		if(point) {
			GEvent.removeListener(myEventListener);
			gNoticeHide();
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openPost('index.php?form=MapUserAjax'+SID_ARG_2ND, 'action=add&lat='+point.y+'&lng='+point.x+'&title='+encodeURIComponent(gmap_marker), function() {
				if(ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
					var response = ajaxRequest.xmlHttpRequest.responseText;
					if(response < 1) {
						alert(gmap_errors[response*-1]);
						return;
					}
					var marker = gRunMarker(point, null, null, response*-1);
					map.addOverlay(marker);
					GEvent.trigger(marker, "click");
					++gmap_count_personal;
					updateCounters();
				}
			});
		}
	});
	return false;
}

/**
 * toggles admin
 * @param ob -> reference to object
 * @param textOn -> language text enable
 * @param textOff -> language text disable
 * @param return -> returns false to skip link processing
 */
function gAdministrate(ob, textOn, textOff) {
	if(gmap_admin) {
		gmap_admin = false;
		gMap(gmap_type_prior); // reload the map
		document.getElementById('main').style.backgroundColor = '';
		document.getElementById('gmapAdminButtons').style.display = 'none';
		ob.replaceChild(document.createTextNode(textOn), ob.firstChild);
	} else {
		gmap_admin = true;
		gMap('admin'); // reload the map
		document.getElementById('main').style.backgroundColor = '#ffeeee';
		document.getElementById('gmapAdminButtons').style.display = 'block';
		ob.replaceChild(document.createTextNode(textOff), ob.firstChild);
	}
	return false;
}

/**
 * sends ajax request to store marker information
 * @param title -> title for the entry
 * @param info -> text for the entry
 * @return -> returns false to skip form processing
 */
function gRequestUpdate(mapID, title, info) {
	var ajaxRequest = new AjaxRequest();
	ajaxRequest.openPost('index.php?form=MapUserAjax'+SID_ARG_2ND, 'action=update&mapID='+mapID+'&title='+encodeURIComponent(title)+'&info='+encodeURIComponent(info), function() { 
		if(ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
			map.closeInfoWindow();
		}
	});

	return false;
}

/**
 * sends ajax request to store marker information
 * @param mapID -> mapID
 * @return -> returns false to skip link processing
 */
function gRequestRemove(mapID) {
	var ajaxRequest = new AjaxRequest();
	ajaxRequest.openPost('index.php?form=MapUserAjax'+SID_ARG_2ND, 'action=remove&mapID='+mapID, function() {
		if(ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
			gMap(gmap_type_current);
		}
	});
	return false;
}

/**
 * creates and/or return a form
 * @param mapID -> mapID
 * @param title -> can be null
 * @param info -> can be null
 * @return -> dom object representing the form
 */
function gForm(mapID, title, info) {
	// create dom objekt for the form
	var gmap_form = document.createElement('form');
	gmap_form.style.width = '300px';
	gmap_form.setAttribute('method', 'post');
	
	// append hidden input for mapID (if existing)
	var hidden = document.createElement('input');
	hidden.setAttribute('name', 'mapID');
	hidden.setAttribute('type', 'hidden');
	hidden.setAttribute('value', mapID);
	gmap_form.appendChild(hidden);
	
	// append input for the title
	gmap_form.appendChild(document.createTextNode(gmap_title+': '));
	var input = document.createElement('input');
	input.style.width = '230px';
	input.setAttribute('name', 'title');
	input.setAttribute('type', 'text');
	input.setAttribute('value', title ? title : gmap_marker);
	gmap_form.appendChild(input);
	
	gmap_form.appendChild(document.createElement('br'));
	
	// append textarea for the text
	gmap_form.appendChild(document.createTextNode(gmap_info+': '));
	var textarea = document.createElement('textarea');
	textarea.setAttribute('name', 'info');
	textarea.style.height = '90px';
	if(info) textarea.appendChild(document.createTextNode(info));
	gmap_form.appendChild(textarea);
	
	// append remove button
	var remove = document.createElement('a');
	remove.style.cssFloat = 'left';
	remove.setAttribute('href', '#');
	remove.onclick = function() { return gRequestRemove(mapID); };
	remove.appendChild(document.createTextNode(gmap_remove));
	gmap_form.appendChild(remove);
	
	// append submit button
	var submit = document.createElement('input');
	submit.style.cssFloat = 'right';
	submit.setAttribute('type', 'submit');
	submit.setAttribute('value', gmap_save);
	gmap_form.appendChild(submit);
	
	// append cancel button
	var cancel = document.createElement('button');
	cancel.style.cssFloat = 'right';
	cancel.onclick = function() { map.closeInfoWindow(); return false; };
	cancel.appendChild(document.createTextNode(gmap_cancel));
	gmap_form.appendChild(cancel);
	gmap_form.onsubmit = function() { return gRequestUpdate(hidden.value, input.value, textarea.value); };

	return gmap_form;
}

/**
 * show/hides the black wall
 * @param trueFalse -> set true to display, false to hide
 */
function gBlack(trueFalse) {
	var dom_loading = document.getElementById('gmap_loading');
	dom_loading.style.display = trueFalse ? "block" : "none";
}

/**
 * displays a message box (unremovable)
 * @param type -> dialog type (WARNING/ERROR)
 * @param message -> html mesage
 */
function gDialog(type, message) {
	if(!gLoading(false))
		return;
	gBlack(true);

	var icon = gmap_wcf+"icon/g-map/";
	switch(type) {
		case 'ERROR':
			icon += "dialog-error.png";
		break;
		case 'WARNING':
			icon += "dialog-warning.png";
		break;
	}

	var text = document.createTextNode(" "+message);

	var img = document.createElement('img');
	img.setAttribute('src', icon);
	img.setAttribute('alt', '');

	var div = document.createElement('div');
	div.setAttribute('width', '150px');
	div.appendChild(img);
	div.appendChild(text);

	var gmap_loading_message = document.getElementById('gmap_loading_message');
	gmap_loading_message.style.display = "block";
	gmap_loading_message.appendChild(div);
}

/**
 * shows/hides loading screen
 * @param trueFalse -> set true to display, false to hide
 */
function gLoading(trueFalse) {
	var dom_loading_img = document.getElementById('gmap_loading_img');
	if(!dom_loading_img)
		return false;
	
	gBlack(trueFalse)
	dom_loading_img.style.display = trueFalse ? "block" : "none";

	return true;
}

/**
 * toggles filter and reloads the map
 * @param id -> 
 * @param trueFalse -> 
 */
function gFilter(id, trueFalse) {
	if(trueFalse == false) {
		ajax_hidegroup.push(id);
	} else {
		var c = new Array();
		for(var i=0; i<ajax_hidegroup.length; i++) {
			if(ajax_hidegroup[i] != id) {
				c.push(ajax_hidegroup[i]);
			}
		}
		ajax_hidegroup = c;
	}
	gMap(gmap_type_current);
}

/**
 * toggles filter options and reloads the map
 * @param opt -> option (e.g. team, online)
 * @param trueFalse -> 
 */
function gFilterOption(opt, trueFalse) {
	ajax_filter[opt] = trueFalse;
	gMap(gmap_type_current);
}

/**
 * adds a new user to the map
 * @param id -> 
 */
function gAddUser(id) {
	ajax_showusers.push(id);
}

/**
 * adds a new group to the controls bar
 * @param callback -> callback function which is set as onchange handler of the checkbox object
 * @param title -> label of the checkbox
 * @param checked -> should the checkbox be checked
 */
function gAddGroup(callback, title, checked) {
	var text = document.createTextNode(title);
	var input = document.createElement('input');
	input.setAttribute('type', 'checkbox');
	var div = document.createElement('div');
	div.style.top = gmap_groups+'px';
	
	div.appendChild(input);
	if(checked) {
		input.checked = true;
		input.defaultChecked = true;
	}
	input.onclick = callback;

	div.appendChild(text);
	
	dom_controls.appendChild(div);
	gmap_groups += 25;
}

/**
 * returns markers in different sizes
 * @param point -> GLatLng object - position longitude/latitude
 * @param headline -> bold printed headline
 * @param userstring -> content string
 * @param count -> usercount
 * @return -> marker object
 */
function gRunMarker(point, headline, userstring, count) {
	var marker, icon;

	if(count == 1) {
		baseIcon = new Array("marker.png", 14, 0, 0);
	} 
	else if(count > 999) {
		baseIcon = new Array("marker0000.png", 50, -16, -9);
	} 
	else if(count > 99) {
		baseIcon = new Array("marker000.png", 40, -12, -10);
	} 
	else if(count > 9) {
		baseIcon = new Array("marker00.png", 32, -8, -8);
	} 
	else if(count < 0 || count > 0) {
		baseIcon = new Array("marker0.png", 24, -4, -8);
	}

	icon = new GIcon();
	icon.image = gmap_wcf+"icon/g-map/"+(count<0?"b":"")+baseIcon[0];
	icon.iconSize = new GSize(baseIcon[1], baseIcon[1]);
	icon.iconAnchor = new GPoint(baseIcon[1]/2, baseIcon[1]/2);
	icon.infoWindowAnchor = new GPoint(baseIcon[1]/2, baseIcon[1]/4);
	
	// options: basic
	var opts = {
		"icon": icon,
		"clickable": true,
		"labelOffset": new GSize(baseIcon[2], baseIcon[3])
	};
	
	// option: text
	if(count > 1) {
		opts.labelText = count;
	}	
	
	if(count < 0 && gmap_admin) { // that's the trick to seperate users from public markers
		var mapID = count*-1;

		// Extend the global Marker Array
		marker = new GMarker(point, {icon: icon, draggable: true});
		marker.info = gForm(mapID, headline, userstring);

		// Add Clickevent
		GEvent.addListener(marker, "click", function() {
			marker.openInfoWindow(marker.info);
			return;
		});

		// Add Dragevent
		GEvent.addListener(marker, "dragend", function() {
			var lat = marker.getPoint().lat();
			var lng = marker.getPoint().lng();
		
			// update
			var ajaxRequest = new AjaxRequest();
			ajaxRequest.openPost('index.php?form=MapUserAjax'+SID_ARG_2ND, 'action=update&mapID='+mapID+'&lat='+lat+'&lng='+lng, function() {
				if(ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
					//pass
				}
			});
		});
		
		
	} else {
		marker = new LabeledMarker(point, opts);

		GEvent.addListener(marker, "click", function() {
			var gmap_b = document.createElement('div');
			gmap_b.className = 'gmap_b';
		
			var gmap_h = document.createElement('div');
			gmap_h.className = 'gmap_h';
			gmap_h.style.backgroundImage = "url('"+RELATIVE_WCF_DIR+"icon/g-map/zoom.gif')";
			gmap_h.setAttribute('onclick', "map.zoomIn();");
			gmap_h.appendChild(document.createTextNode(headline));
		
			gmap_b.appendChild(gmap_h);
			gmap_b.innerHTML += userstring;
		
			marker.openInfoWindow(gmap_b);
		});
	}

	return marker;
}

/**
 * updates visible counters
 */
function updateCounters() {
	if(document.getElementById('gmap_usercount'))
		document.getElementById('gmap_usercount').innerHTML = gmap_count_users;
	if(document.getElementById('gmap_markercount'))
		document.getElementById('gmap_markercount').innerHTML = gmap_count_personal;
}

/**
 * displays a notice
 */
function gNotice(text, timeout) {
	document.getElementById('gmap_notice').innerHTML = text;
	document.getElementById('gmap_notice').style.display = 'block';
	if(timeout >= 0) setTimeout("gNoticeHide()", timeout*1000);
}

/**
 * fades notice out
 */
function gNoticeHide() {
	document.getElementById('gmap_notice').style.display = 'none';
	//for(var i=75; i>=0; i--) {}
}

/**
 * loads userdata from ajax and creates markers
 * @param type -> grouping parameter (e.g. posts, users, admin)
 */
function gMap(type) {
	if(type != 'admin')
		gmap_type_prior = type;
	gmap_type_current = type;

	gLoading(true); // show loading message
	gmap_count_users = 0;
	gmap_count_personal = 0;

	var url = "index.php?page=MapAjax&type="+type;
	url += ajax_hidegroup.length > 0 ? "&groups="+ajax_hidegroup.join(",") : "";
	url += ajax_showusers.length > 0 ? "&users="+ajax_showusers.join(",") : "";
	for(var filter in ajax_filter) {
		if(ajax_filter[filter]) url += "&"+filter;
	}
	url += SID_ARG_2ND;
	url += "&acid="+Math.random();

	GDownloadUrl(url, function(data, responseCode) {
		var xml = GXml.parse(data);
		var markers = xml.documentElement.getElementsByTagName('p');
		var errorcodes = xml.documentElement.getElementsByTagName('errors')[0];
		var distance = xml.documentElement.getElementsByTagName('dist')[0];
		var bounds = xml.documentElement.getElementsByTagName('bounds')[0];

		// display errorcodes
		if(errorcodes != null) {
			var errorcodes_arr = errorcodes.firstChild.data.split(";");
			var messages = new Array();
			for(var i=0; i<errorcodes_arr.length; i++) {
				messages.push(gmap_errors[errorcodes_arr[i]]);
			}
			gDialog("ERROR",messages.join("<br/>"));
			return;
		}
		
		// zoom to fit bounds
		if(bounds != null) {
			bounds = bounds.firstChild.data.split(",");
			var gbounds = new GLatLngBounds;
			var latlng1 = new GLatLng(bounds[0],bounds[1]);
			var latlng2 = new GLatLng(bounds[2],bounds[3]);
			gbounds.extend(new GLatLng(latlng1));
			gbounds.extend(new GLatLng(latlng2));
			//var zoom1 = map.getBoundsZoomLevel(gbounds);
			//alert(map.getBoundsZoomLevel(gbounds));
			//alert(zoom1);
			//var zoom2 = Math.min(map.getCurrentMapType().getMaximumResolution(latlng1), map.getCurrentMapType().getMaximumResolution(latlng2));
			//alert(zoom2);
			//map.setCenter(gbounds.getCenter(), Math.min(zoom1,zoom1));
		}


		// reset map and lines
		map.clearOverlays();
		gmap_line = new Array();
		var link;

		for(var i=0; i<markers.length; i++) {
			var count = 0;
			var users = markers[i].getElementsByTagName('u');
			var lat = parseFloat(markers[i].getAttribute("lat"));
			var lng = parseFloat(markers[i].getAttribute("lng"));
			var headline = markers[i].getAttribute("h");
			
			var userstring = "";
			for(var j=0; j<users.length; j++) {
				if(users[j].getAttribute("id") > 0) { // that's the trick to seperate users from public markers
					gmap_count_users++;
					count++;
					link = 'index.php?page=User&amp;userID='+users[j].getAttribute("id"); 
					userstring += (count < 2 ? "" : ", ") + '<a href="'+link+'">'+(users[j].firstChild?users[j].firstChild.data:"-")+'</a>';
				} else {
					gmap_count_personal++;
					count = users[j].getAttribute("id");
					userstring += users[j].firstChild ? users[j].firstChild.data : "-";
				}
			}
			map.addOverlay(gRunMarker(new GLatLng(lat,lng), headline, userstring, count));
			
			if(type == "distance") {
				gmap_line.push(new GLatLng(lat, lng));
			}
		}
		updateCounters();
		
		// update gline
		if(gmap_line.length > 1) map.addOverlay(new GPolyline(gmap_line));
		
		gLoading(false); // hide loading message
		
		// display distances
		if(distance != null) {
			gNotice(distance.firstChild.data+" km", 5);
		}
	});
}
