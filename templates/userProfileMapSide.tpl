<div id="userProfileMap">
	<div class="border">
		<div class="containerHead">
			<h3>{lang}wcf.user.profile.map{/lang}</h3>
		</div>
		<div class="container-1" id="userMap">
			<div id="userMapCanvas" style="width: 100%; height: 260px; overflow:hidden"></div>
		</div>
	</div>
</div>
{include file='gmapConstants'}
<script src="{@RELATIVE_WCF_DIR}js/gmap/Map3.class.js" type="text/javascript"></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&amp;language={@$this->language->getLanguageCode()}"></script>
<script type="text/javascript">
	//<![CDATA[
        onloadEvents.push(function() {
		var map = new Map3('userMap');
		map.loadMarkers([{
			latitude: {$coordinate.lat},
			longitude: {$coordinate.lon},
		}]);
		map.showMap();
        });
	//]]>
</script>
