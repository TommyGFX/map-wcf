<div id="userProfileMap">
	<div class="border">
		<div class="containerHead">
			<div class="containerIcon"><img src="{icon}mapM.png{/icon}" alt="" title="{lang}wcf.map.copyright{/lang}"/></div>
			<h3 class="containerContent">{lang}wcf.user.profile.map{/lang} </h3>
		</div>
		<div class="container-1" id="userMap">
			<div id="userMapCanvas" style="width: 100%; height: 260px; overflow:hidden"></div>
		</div>
	</div>
</div>
{include file='gmapConstants'}
<script src="{@RELATIVE_WCF_DIR}js/gmap/Map.class.js" type="text/javascript"></script>
<script type="text/javascript">
	//<![CDATA[
	if (GMAP_API_KEY != '')  { 
	        document.write('<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;hl={@$this->language->getLanguageCode()}&amp;key=' + GMAP_API_KEY + '&amp;oe={CHARSET}" type="text/javascript"><\/script>');
	        onloadEvents.push(function() {
	                if (GBrowserIsCompatible()) {
				var map = new Map('userMap');
				coordinates = new GLatLng({$coordinate.lat}, {$coordinate.lon});
				map.setCoordinates(coordinates);
	                }
	        });
	}
	//]]>
</script>
