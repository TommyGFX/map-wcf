<?php
require_once(WCF_DIR.'lib/data/user/User.class.php');

/**
 * discovers geoposition to data and updates database
 *
 * @package     de.gmap.wcf.util
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapDiscover {
	protected $columns;
	protected $debug;

	/**
         * fetchs columns
         */
        public function __construct($debug=false) {
        	$this->debug = $debug;
		$arr = array();

		$arr['location'] = "userOption".User::getUserOptionID('location');
		$arr['coord'] = "userOption".User::getUserOptionID('map_coord');
		$arr['street'] = "userOption".User::getUserOptionID('map_street');
		$arr['city'] = "userOption".User::getUserOptionID('map_city');
		$arr['country'] = "userOption".User::getUserOptionID('map_country');
		$arr['zip'] = "userOption".User::getUserOptionID('map_zip');
		$arr['enable'] = "userOption".User::getUserOptionID('map_enable');
		$arr['lastlookup'] = "userOption".User::getUserOptionID('map_lastlookup');
		
		if(count(array_filter($arr, create_function('$a','return $a == "userOption";'))) > 0) {
			require_once(WCF_DIR . 'lib/system/exception/SystemException.class.php');
			throw new SystemException("userOptions are not set.\n\n".print_r($arr,1));
		}
		
		$this->columns = $arr;
        }
        
        /**
         * get columns
         */
        public function getColumns() {
        	return $this->columns;
        }
        
        /**
	 * ask google for geopositions
	 * @param street
	 * @param zip
	 * @param city
	 * @param country
	 */
	public function search($street, $zip="", $city="", $country="") {
		$res = array('x'=>"0",'y'=>"0");
		$c = $this->columns;

		$lookupstring = sprintf("%s %s %s %s", $street, $zip, $city, $country);
		$lookupstring = trim($lookupstring);
		$lookupstring = urlencode($lookupstring);

		// Debug: forces ip without DNS-Lookup. 
		// $req_url = "maps.google.com";
		$req_url = MAP_CRON_IP ;
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
		} else {
			if($this->debug) {
				require_once(WCF_DIR . 'lib/system/exception/SystemException.class.php');
				throw new SystemException("$req_url - $errstr ($errno)");
			}
		}
		
		return $res;
	}

	/**
	 * ask google for geopositions
	 * @param street
	 * @param zip
	 * @param city
	 * @param country
	 */
	public function update($street, $zip, $city, $country) {
		$c = $this->columns;
		$res = $this->search($street, $zip, $city, $country);

		$sql_update = "UPDATE	wcf".WCF_N."_user_option_value 
			SET 
					{$c['coord']} 		= '".$res['x'].",".$res['y']."', 
					{$c['lastlookup']}	= CONCAT({$c['street']},{$c['zip']},{$c['city']},{$c['country']}) 
			WHERE		{$c['enable']} 		= 1
			AND		{$c['street']} 		= '".escapeString($street)."'
			AND 		{$c['zip']} 		= '".escapeString($zip)."'
			AND 		{$c['city']} 		= '".escapeString($city)."'
			AND 		{$c['country']} 	= '".escapeString($country)."'; ";

		WCF::getDB()->sendQuery($sql_update);
	}
}
?>
