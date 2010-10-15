{include file="documentHeader"}
<head>
	<title>{lang}wcf.header.menu.map{/lang} - {PAGE_TITLE}</title>

	{include file='headInclude' sandbox=false}
	{include file='gmapConstants'}
	<script type="text/javascript">
	//<![CDATA[
	GMAP_ZOOM = 5;
	GMAP_ENABLE_STREETVIEW = 1; 
	GMAP_MAP_CONTROL = 'off';
	//]]>
	</script>
	<script src="{@RELATIVE_WCF_DIR}js/gmap/Map.class.js" type="text/javascript"></script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/AjaxRequest.class.js"></script>
	<script type="text/javascript">
	//<![CDATA[
	if (GMAP_API_KEY != '')  { 
		document.write('<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;hl={@$this->language->getLanguageCode()}&amp;key=' + GMAP_API_KEY + '&amp;oe={CHARSET}" type="text/javascript"><\/script>');
	}
	//]]>
	</script>
	<script type="text/javascript">
	//<![CDATA[
	if (GMAP_API_KEY != '')  {
		document.write('<script src="{@RELATIVE_WCF_DIR}js/gmap/ClusterMarker.class.js" type="text/javascript"><\/script>');
		document.write('<script src="{@RELATIVE_WCF_DIR}js/gmap/StreetViewControl.class.js" type="text/javascript"><\/script>');
		document.write('<script src="{@RELATIVE_WCF_DIR}js/gmap/AjaxMap.class.js" type="text/javascript"><\/script>');
		onloadEvents.push(function() {
			if (GBrowserIsCompatible()) {
				var gmap = new AjaxMap('index.php?page=MapAjax', 'gmap');
				gmap.registerEvent(function(map) {
					return function() {
						if(GMAP_ENABLE_STREETVIEW) map.gmap.addControl(new StreetViewControl());
					}
				}(gmap));
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
		<img src="{icon}mapL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.header.menu.map{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="border">
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
