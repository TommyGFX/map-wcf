<?php

/**
 * gets several positions and returns a clustered array
 *
 * @author	Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class Gmap extends DatabaseObject {
        
        /**
	 * ask google for geopositions
	 * @param location
	 */
	public static function search($location) {
		$res = null;

		$lookupstring = urlencode(trim($location));

		$req_url = "maps.google.com";
		$io = @fsockopen($req_url, 80, $errno, $errstr, 5 );
		if ($io) {
			$send  = "GET /maps/geo?q=".$lookupstring."&key=".MAP_API."&output=csv HTTP/1.1\r\n";
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
		
		return $res;
	}
}
