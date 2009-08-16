{include file="documentHeader"}
<head>
	<title>{lang}wcf.header.menu.map{/lang} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	{include file='gmapConstants'}
	<script src="{@RELATIVE_WCF_DIR}js/gmap/Map.class.js" type="text/javascript"></script>
	<script type="text/javascript">
		//<![CDATA[
		if (GMAP_API_KEY != '')  { 
		        document.write('<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;hl={@$this->language->getLanguageCode()}&amp;key=' + GMAP_API_KEY + '&amp;oe={CHARSET}" type="text/javascript"><\/script>');
		        onloadEvents.push(function() {
		                if (GBrowserIsCompatible()) {
		                        var gmap = new Map('{@$id}');
		                        {if $location|isset}gmap.setLocation('{$location|encodeJS}');
		                        {elseif $latitude|isset && $longitude|isset}
		                                var coordinates = new GLatLng({@$latitude}, {@$longitude});
		                                gmap.setCoordinates(coordinates);
		                        {/if}
		                        
		                        // init route
		                        var gmapRoute = new MapRoute(gmap);
		                }
		        });
		}
		//]]>
	</script>

	<script type="text/javascript">
	//<![CDATA[
		var gmap_wcf = '{@RELATIVE_WCF_DIR}';
		var gmap_zoom = '{lang}wcf.map.zoom{/lang}';
		var gmap_marker = '{lang}wcf.map.marker{/lang}';
		var gmap_url = '{lang}wcf.map.markerUrl{/lang}';
		var gmap_title = '{lang}wcf.map.markerTitle{/lang}';
		var gmap_info = '{lang}wcf.map.markerInfo{/lang}';
		var gmap_save = '{lang}wcf.map.markerSave{/lang}';
		var gmap_cancel = '{lang}wcf.map.markerCancel{/lang}';
		var gmap_remove = '{lang}wcf.map.markerRemove{/lang}';
	//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/labeled_marker.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/g-map.js"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
</head>
<body>
{include file="header" sandbox=false}
<div id="main">
	<ul class="breadCrumbs">
		<li><a href="index.php{@SID_ARG_1ST}"><img src="icon/indexS.png" alt="" /> <span>{PAGE_TITLE}</span></a> &raquo;</li>
	</ul>
	
	{if $gmap_admin}<div id="mapAdministration">
		<a href="#" onclick="return gAdministrate(this, '{lang}wcf.map.administrateOn{/lang}', '{lang}wcf.map.administrateOff{/lang}')">{lang}wcf.map.administrateOn{/lang}</a>
		<div class="largeButtons" id="gmapAdminButtons">
			<ul>
				<li><a href="#" onclick="return gRequestAdd('{lang}wcf.map.markerClickToAdd{/lang}');"><img src="icon/g-map/markerAddM.png" alt="" /> <span>{lang}wcf.map.markerAdd{/lang}</span></a></li>
			</ul>
		</div>
	</div>{/if}
	
	<div class="mainHeadline" style="clear:none">
		<img src="icon/glob48.png" alt="" title="{lang}wcf.map.copyright{/lang}" />
		<div class="headlineContainer">
			<h2> {lang}wcf.header.menu.map{/lang}</h2>
			<b id="gmap_usercount">...</b> {lang}wcf.map.counter_user{/lang} / <b id="gmap_markercount">..</b> {lang}wcf.map.counter_marker{/lang}
		</div>
	</div>
	<div class="border">
		<div class="container-1">
			<div style="position:relative">
				<!-- Map //-->
				<div id="map" style="width: 100%; height: 500px"></div>
				
				<!-- GroupFilter sub //-->
				<div id="gmap_controls_sub"></div>
				
				<!-- Controls //-->
				<form method="post" action="" onsubmit="return false">
					<div id="gmap_controls"></div>
				</form>
				
				<!-- Messages //-->
				<div id="gmap_notice"></div>

				<!-- Loading //-->
				<div id="gmap_loading">&nbsp;</div>
				<div id="gmap_loading_message">&nbsp;</div>
				
				<div id="gmap_loading_img">
					<img src="icon/g-map/loading.gif" alt="" />
				</div>
			</div>
			
			<script type="text/javascript">
			//<![CDATA[
				var dom_controls;
				var gmap_errors = new Array('{lang}wcf.map.forbidden{/lang}', '{lang}wcf.map.tomuchentries{/lang}');
				var ajax_filter = new Object();
				ajax_filter.markers = true;
				var map = new GMap2(document.getElementById("map"));
				var center = new GLatLng({@MAP_BIGMAP_CENTER});
				map.setCenter(center, {@MAP_BIGMAP_ZOOM});
				{if $additionalVars|isset}{@$additionalVars}{/if}

				{if MAP_BIGMAP_CTL_MOUSEWHEELZOOM}map.enableScrollWheelZoom();{/if}
				{if MAP_BIGMAP_CTL_NAVI}map.addControl(new GSmallMapControl());{/if}
				{if MAP_BIGMAP_CTL_TYPE}
					map.addMapType(G_PHYSICAL_MAP);
					map.addMapType(G_SATELLITE_3D_MAP);
					map.addControl(new GHierarchicalMapTypeControl());
				{/if}
				{if MAP_BIGMAP_CTL_SCAL}map.addControl(new GScaleControl());{/if}
				{if MAP_BIGMAP_CTL_OVER}map.addControl(new GOverviewMapControl());{/if}
				{if MAP_BIGMAP_TYPE == "m"}map.setMapType(G_NORMAL_MAP);{/if}
				{if MAP_BIGMAP_TYPE == "h"}map.setMapType(G_HYBRID_MAP);{/if}
				{if MAP_BIGMAP_TYPE == "s"}map.setMapType(G_SATELLITE_MAP);{/if}
				
				// show filter team
				{if MAP_BIGMAP_GROUPFILTER == "i"}dom_controls = document.getElementById('gmap_controls');{/if}
				{if MAP_BIGMAP_GROUPFILTER == "s"}dom_controls = document.getElementById('gmap_controls_sub');{/if}
				{if MAP_BIGMAP_GROUPFILTER != "n" && MAP_GROUP_TEAM}
					{if $this->user->getPermission("user.map.canViewPersonal")}
					gAddGroup(function(){ gFilterOption('markers', this.checked); }, '{lang}wcf.map.marker{/lang}', ajax_filter.markers);
					{/if}
					{foreach from=$gmap_groups item=$group}
					gAddGroup(function(){ gFilter('{$group[0]}', this.checked); },'{$group[1]}', {if $group[2]}1{else}0{/if});
				        {/foreach}
			        {/if}
			        
			        // show filter buttons
			        {if $additionalFilters|isset}{@$additionalFilters}{/if}
			        
			        // load users
			        {foreach from=$gmap_users item=$user}
				gAddUser({$user});
			        {/foreach}
				
				// load markers into the map
				gMap('{$gmap_type}');
			//]]>
			</script>
	  	</div>
	  	<a href="http://trac.easy-coding.de/trac/g-map" class="externalURL" style="float:right">{lang}wcf.map.copyright_small{/lang}</a>
	</div>

</div>
{include file="footer" sandbox=false}

</body>
</html>
