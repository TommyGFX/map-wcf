<?php

class Gmap extends Foo {
        
        /**
	 * ask google for geopositions
	 * @param location
	 */
	public static function search($location) {
		$res = array(
			'x' => 0,
			'y' => 0
		);

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
					$res['x'] = trim($hits[1]);
					$res['y'] = trim($hits[2]);
					break;
				}
			}
			fclose($io);
		}
		
		return $res;
	}

	/**
	 * try to update database position
	 *
	 * @param location
	 */
	public static function update($location) {
		$column = "userOption".User::getUserOptionID('location');
		if(empty($column)) return;

		// ask geocoder
		$res = self::search($street, $zip, $city, $country);

		// update all locations
		$sql = "UPDATE		wcf".WCF_N."_user
			USING		wcf".WCF_N."_user_option_value
			INNER JOIN	wcf".WCF_N."_user USING(userID)
			SET 		coords = PointFromText('POINT(".$res['x']." ".$res['y'].")')
			WHERE		".$column." = '".escapeString($location)."';";
		WCF::getDB()->sendQuery($sql);
	}
        
        /**
         *
         * @param pt1 -> point1 in format array(lat, lng)
         * @param pt2 -> point2 in format array(lat, lng)
         */
        public static function getDistance($pt1, $pt2) {
        	$lat1 = $pt1[0];
        	$lng1 = $pt1[1];
        	$lat2 = $pt2[0];
        	$lng2 = $pt2[1];

		return round(acos((sin($lat1) * sin($lat2)) + (cos($lat1) * cos($lat2) * cos($lng1 - $lng2))) * 6380 / 100 * 1.609344,2);
	}
}
