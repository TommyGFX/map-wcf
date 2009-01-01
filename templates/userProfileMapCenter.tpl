<div class="userProfileContent">
	<div class="border">
		<div class="containerHead">
			<div class="containerIcon"><img src="{@RELATIVE_WCF_DIR}icon/glob24.png" alt="" /> </div>
			<h3 class="containerContent">{lang}wcf.user.profile.map{/lang}</h3>
		</div>
		<div>

<div id="mapcenter" style="width:100%; height: 300px; overflow:hidden"></div>								

<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;key={$gmap_map_key}&amp;oe={CHARSET}" type="text/javascript"></script>
<script type="text/javascript">
//<![CDATA[
var usercoord = new GLatLng({@$user->map_coord});
var mapcenter = new GMap2(document.getElementById("mapcenter"));
mapcenter.setCenter(usercoord, {@MAP_USERMAP_ZOOM});
var marker = new GMarker(usercoord);
GEvent.addListener(marker, "click", function() {
	var url = 'index.php?page=Map&type=distance&users={@$user->userID}{@SID_ARG_2ND}';
	window.location.href = url;
	});
mapcenter.addOverlay(marker); 
{if MAP_USERMAP_CTL_NAVI}mapcenter.addControl(new GSmallMapControl());{/if}
{if MAP_USERMAP_CTL_TYPE}
map.addMapType(G_PHYSICAL_MAP);
map.addControl(new GHierarchicalMapTypeControl());
{/if}
{if MAP_USERMAP_CTL_SCAL}mapcenter.addControl(new GScaleControl());{/if}
{if MAP_USERMAP_CTL_OVER}mapcenter.addControl(new GOverviewMapControl());{/if}
{if MAP_USERMAP_TYPE == "m"}mapcenter.setMapType(G_NORMAL_MAP);{/if}
{if MAP_USERMAP_TYPE == "h"}mapcenter.setMapType(G_HYBRID_MAP);{/if}
{if MAP_USERMAP_TYPE == "s"}mapcenter.setMapType(G_SATELLITE_MAP);{/if}
//]]>
</script>

		</div>
	</div>
</div>
