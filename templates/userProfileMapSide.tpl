<div id="userProfileMap">
	<div class="border">
		<div class="containerHead">
			<div class="containerIcon"><img src="{@RELATIVE_WCF_DIR}icon/glob24.png" alt="" title="{lang}wcf.map.copyright{/lang}"/></div>
			<h3 class="containerContent">{lang}wcf.user.profile.map{/lang} </h3>
		</div>
		<div class="container-1">
			<div id="map" style="width: 100%; height: 260px; overflow:hidden"></div>
		</div>
	</div>
</div>
<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;key={$gmap_map_key}&amp;oe={CHARSET}" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
var usercoordside = new GLatLng({@$user->map_coord}); 
var mapside = new GMap2(document.getElementById("map")); 
mapside.setCenter(usercoordside, {@MAP_USERMAP_ZOOM}); 
var markerside = new GMarker(usercoordside); 
GEvent.addListener(markerside, "click", function() {
	var url = 'index.php?page=Map&type=distance&users={@$user->userID}{@SID_ARG_2ND}';
	window.location.href = url;
	});
mapside.addOverlay(markerside); 
{if MAP_USERMAP_TYPE == "m"}mapside.setMapType(G_NORMAL_MAP);{/if}
{if MAP_USERMAP_TYPE == "h"}mapside.setMapType(G_HYBRID_MAP);{/if}
{if MAP_USERMAP_TYPE == "s"}mapside.setMapType(G_SATELLITE_MAP);{/if}
{if MAP_USERMAP_CTL_NAVI}mapside.addControl(new GSmallMapControl());{/if}
//]]>
</script>
