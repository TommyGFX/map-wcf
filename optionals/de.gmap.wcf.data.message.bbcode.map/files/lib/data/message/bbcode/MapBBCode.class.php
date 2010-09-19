<?php
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeParser.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCode.class.php');

/**
 * Fetchs position data from google maps and stores them in database
 *
 * @package     de.gmap.wcf.data.message.bbcode
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapBBCode implements BBCode {

	/**
	 * parses GPX data
	 */
	private function parseGPX($data, &$stream, &$zoom) {
		//<gpx
		$lines;
		$points;
	}
	
	/**
	 * parses KML data
	 */
	private function parseKML($data, &$stream, &$zoom) {
		//<kml
		$lines;
		$points;
	}
	
	/**
	 * parses CSV data
	 */
	private function parseCSV($data, &$stream, &$zoom) {
		$lines;
		$points;
	}
	
	/**
	 * parses simple coordinates
	 * e.g. 46.58872,11.14654
	 */
	private function parseCoordinate($data, &$stream, &$zoom) {
		$first = array_shift($data);
		
		$stream[] = $first;

		if(count($data) > 0) {
			$this->parseCoordinate($data, $stream, $zoom);
		}
	}
	
	/**
	 * parses maps.google.com url
	 * e.g. http://maps.google.de/maps?q=Aachen&hl=de&ie=UTF8&z=11&iwloc=addr&om=1&ll=60.826325,6.117325
	 */
	private function parseMapsGoogle($data, &$stream, &$zoom) {
		$first = array_shift($data);

		$url = parse_url($first);
		parse_str($url['query'], $output);
		if(isset($output['ll'])) {
			$stream[] = $output['ll'];
		} else if(isset($output['sll'])) {
			$stream[] = $output['sll'];
		}

		if(isset($output['z'])) {
			$zoom = intval($output['z']);
		}

		if(count($data) > 0) {
			$this->parseMapsGoogle($data, $stream, $zoom);
		}
	}	
	
	/**
	 * parses geocoder request
	 * e.g. Berlin deutschland
	 */
	private function parseGeocoder(&$data, &$stream, &$zoom) {
		$first = array_shift($data);
		
		require_once(WCF_DIR.'lib/data/gmap/GmapApi.class.php');
		$api = new GmapApi();
		$res = $api->search($first);
		if($res) {
			$stream[] = $res['lat'].','.$res['lon'];
		}

		if(count($data) > 0) {
			$this->parseGeocoder($data, $stream, $zoom);
		}
	}
	
	/**
	 *
	 */
	private function show($stream, $zoom) {
		$bounds = new BoundsUtil();
		$id = rand();

		$code = '<div id="map'.$id.'" style="width: 100%; height: 300px;"></div>
		<script src="http://maps.google.com/maps?file=api&amp;v=2.118&amp;key='.$this->map_key.'&amp;oe='.CHARSET.'" type="text/javascript"></script>
		<script type="text/javascript">
		//<![CDATA[
		onloadEvents.push(function() {
		var map = new GMap2(document.getElementById("map'.$id.'"));'."\n";
		
		if(count($stream) == 0) {
			$stream[] = '37.425525,-122.085743'; // Google Inc
		}

		// line
		$line = "var line = new Array();\n";
		foreach($stream as $pt) {
			list($lat,$lng) = explode(',',$pt);
			$bounds->add($lat, $lng);

			$line .= "line.push(new GLatLng(".$pt."));\n";
			$line .= "map.addOverlay(new GMarker(new GLatLng(".$pt.")));\n";
		}
		$line .= "map.addOverlay(new GPolyline(line));\n";

		if(count($stream) > 1) {
			list($left,$top,$right,$bottom) = explode(',', $bounds->__toString());
			$centerx = ($left+$right)/2;
			$centery = ($top+$bottom)/2;
			$gzoom = $zoom === null ? 'map.getBoundsZoomLevel(bound)' : $zoom;
			$code .= 'var bound = new GLatLngBounds();
			bound.extend(new GLatLng('.$left.', '.$bottom.'));
			bound.extend(new GLatLng('.$right.', '.$top.'));
			map.setCenter(new GLatLng('.$centerx.', '.$centery.'), '.$gzoom.');'."\n";
		} else {
			$gzoom = $zoom === null ? 8 : $zoom;
			$code .= "map.setCenter(new GLatLng(".$stream[0]."), ".$gzoom.");\n";
		}

		$code .= $line;
		
		$code .= "map.addControl(new GSmallMapControl());
		map.addMapType(G_PHYSICAL_MAP);
		map.setMapType(G_HYBRID_MAP);
		map.addControl(new GHierarchicalMapTypeControl());
		});
		//]]></script>";
		return $code;
	}
	

	/**
	 * @see BBCode::getParsedTag()
	 */
	public function getParsedTag($openingTag, $content, $closingTag, BBCodeParser $parser) {

		// TODO: feature disabled
		return $content;
	
		$zoom = isset($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : null;
		$content = explode("\n", StringUtil::unifyNewlines($content));
		
		if(count($content) == 0) return $content;
		
		$stream = array();
		
		// analyze content to choose parser
		if ($parser->getOutputType() == 'text/html') {
			if(preg_match('/^http:\/\/maps\.google\./', $content[0], $matches)) {
				$this->parseMapsGoogle($content, $stream, $zoom);

			} else if(preg_match('/^\d+\.\d+[ ]{0,1},[ ]{0,1}\d+.\d+/', $content[0], $matches)) {
				$this->parseCoordinate($content, $stream, $zoom);

			} else {
				$this->parseGeocoder($content, $stream, $zoom);
			}

			return $this->show($stream, $zoom);
		}
		else if ($parser->getOutputType() == 'text/plain') {
			return $content;
		}
	}
}
?>
