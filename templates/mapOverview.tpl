{include file="documentHeader"}
<head>
	<title>{lang}wcf.header.menu.map{/lang} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	{include file='gmapConstants'}
	<script src="{@RELATIVE_WCF_DIR}js/gmap/Map.class.js" type="text/javascript"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		if (GMAP_API_KEY != '')  { 
			document.write('<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;hl={@$this->language->getLanguageCode()}&amp;key=' + GMAP_API_KEY + '&amp;oe={CHARSET}" type="text/javascript"><\/script>');
			onloadEvents.push(function() {
				if (GBrowserIsCompatible()) {

					var AjaxMap = function(url, divID, switchable) {
						this.url = url;
						this.constructor(divID, switchable);

						this.update = function() {
							url = this.url;
							
							if(this.mapInitialized) {
								url += '&zoom='+this.gmap.getZoom();
								url += '&bounds='+this.gmap.getBounds();
								url += '&initialized=1';
							}

							var ajaxRequest = new AjaxRequest();
							ajaxRequest.openGet(url + SID_ARG_2ND, function(map) {
								return function() {
									if(ajaxRequest.xmlHttpRequest.readyState == 4 && ajaxRequest.xmlHttpRequest.status == 200) {
										var data = eval('(' + ajaxRequest.xmlHttpRequest.responseText + ')');
										var coordinates;

										if(map.mapInitialized) {
											map.gmap.clearOverlays();
											for(var i in data) {
												coordinates = new GLatLng(data[i].lat, data[i].lon);
												map.gmap.addOverlay(new GMarker(coordinates));
											}
										} else {
											coordinates = new GLatLng(data[0].lat, data[0].lon);
											map.setCoordinates(coordinates);
											map.gmap.clearOverlays();
											
											map.update();
											map.setEvents();
										}
									}
								};
							}(this));
						};
						
						this.setEvents = function() {
							GEvent.addListener(this.gmap, "moveend", function(map) {
								return function() {
									map.update();
								}
							}(this));
						}
					};
					AjaxMap.prototype = new Map();

					gmap = new AjaxMap('index.php?page=MapAjax', 'gmap');
					gmap.update();
				}
			});
		}
		//]]>
	</script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file="header" sandbox=false}
<div id="main">
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}gmapL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.header.menu.map{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="border content">
		<div class="container-1">
			<div style="position:relative">
				<div id="gmap"><div id="gmapCanvas" style="width: 100%; height: 500px"></div></div>
			</div>
	  	</div>
	  	<a href="http://trac.easy-coding.de/trac/wcf/wiki/Gmap" class="externalURL" style="float:right">{lang}wcf.map.copyright.small{/lang}</a>
	</div>
</div>

{include file="footer" sandbox=false}
</body>
</html>
