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
		
		list($lat, $lon) = explode(",", $first);
		$stream[] = array(
			'lat' => $lat,
			'lon' => $lon
		);

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
		if(isset($output['ll']) && $output['ll']) {
			list($lat, $lon) = explode(",", $output['ll']);
			$stream[] = array(
				'lat' => $lat,
				'lon' => $lon
			);
		} else if(isset($output['sll']) && $output['sll']) {
			list($lat, $lon) = explode(",", $output['sll']);
			$stream[] = array(
				'lat' => $lat,
				'lon' => $lon
			);
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
			$stream[] = array(
				'lat' => $res['lat'],
				'lon' => $res['lon']
			);
		}

		if(count($data) > 0) {
			$this->parseGeocoder($data, $stream, $zoom);
		}
	}
	
	/**
	 *
	 */
	private function show($stream, $zoom) {
		WCF::getTPL()->assign(array(
			'bbcodemap_zoom' => $zoom,
			'bbcodemap_data' => $stream
		));
		return WCF::getTPL()->fetch('mapBBCode');
	}
	

	/**
	 * @see BBCode::getParsedTag()
	 */
	public function getParsedTag($openingTag, $content, $closingTag, BBCodeParser $parser) {
	
		$zoom = isset($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : GMAP_ZOOM;
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
