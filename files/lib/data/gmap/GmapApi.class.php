<?php

/**
 * gets several positions and returns a clustered array
 *
 * @author	Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GmapApi extends DatabaseObject {
	protected $apikey = null;
	protected $cache_search = array();

	/**
	 *
	 */
	public function __construct() {

		$res = null;

		$apikey = GMAP_API_KEY;
		$apikey = StringUtil::unifyNewlines($apikey);
		$apikey = explode("\n", $apikey);
		$apikey = $apikey[0];
		$apikey = explode(":", $apikey);
		if(count($apikey) == 2) {
			$this->apikey = $apikey[1];
		}
	}

	/**
	 * is active? api key existent?
	 */
	public function isActive() {
		return !empty($this->apikey);
	}
        
        /**
	 * ask google for geopositions
	 * @param location
	 */
	public function search($location) {

		if(!$this->isActive()) {
			return;
		}
		
		$res = array();

		$lookupstring = urlencode(StringUtil::trim($location));
		if(isset($this->cache_search[$lookupstring])) {
			return $this->cache_search[$lookupstring];
		}

		$req_url = "maps.google.com";
		$io = @fsockopen($req_url, 80, $errno, $errstr, 5 );
		if ($io) {
			$send  = "GET /maps/geo?q=".$lookupstring."&key=".$this->apikey."&output=csv HTTP/1.1\r\n";
			$send .= "Host: maps.google.com\r\n";
			$send .= "Accept-Language: de, en;q=0.50\r\n";
			$send .= "Connection: Close\r\n\r\n";
			fputs($io, $send);

			while (!feof($io)) {
				$send = fgets($io, 4096);
				if (preg_match('/^200,[^,]+,([^,]+),([^,]+)$/', $send, $hits)) {
					$res = array(
						'lat' => trim($hits[1]),
						'lon' => trim($hits[2])
					);
					break;
				}
			}
			fclose($io);
		}

		return $this->cache_search[$lookupstring] = $res;
	}
}
