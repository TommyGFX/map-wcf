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
	  	</div>
	  	<a href="http://trac.easy-coding.de/trac/g-map" class="externalURL" style="float:right">{lang}wcf.map.copyright_small{/lang}</a>
	</div>

</div>
{include file="footer" sandbox=false}

</body>
</html>
