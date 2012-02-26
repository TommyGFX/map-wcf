<?php

/**
 * gets several positions and returns a clustered array
 *
 * @author	Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GmapApi extends DatabaseObject {
	protected $cache_search = array();

	/**
	 * is active? api key existent?
	 * @deprecated no api key needed any longer
	 * @return	boolean
	 */
	public function isActive() {
		return true;
	}
	
	/**
	 * @var array<string>
	 */
	public function getFields() {
		if(defined('GMAP_CUSTOMINPUT') && ($const = GMAP_CUSTOMINPUT) && !empty($const)) {
			$tmp = explode(",", GMAP_CUSTOMINPUT);
			$cols = array();
			foreach($tmp as $field) {
				$col = User::getUserOptionID($field);
				if($col) {
					$cols[] = $field;
				}
			}
		} else {
			$cols = array('location');
		}
		return $cols;
	}
	
	/**
	 * @var string
	 */
	public function getColumn() {
		$cols = array();
		foreach($this->getFields() as $field) {
			$cols[] = User::getUserOptionID($field);
		}

		$col = array();
		foreach($cols as $id) {
			$col[] = 'CONCAT(userOption'.$id.', IF(userOption'.$id.' = "", "", " "))';
		}
		$col = 'CONCAT('.implode(',', $col).')';
		
		return $col;
	}
        
        /**
	 * ask google for geopositions
	 * 
	 * @param 	$location		string
	 * @return				array<float>	keys are lat and lon
	 */
	public function search($location) {
		if (CHARSET != 'UTF-8') {
			$location = StringUtil::convertEncoding(CHARSET, 'UTF-8', $location);
		}
		$lookupstring = urlencode(StringUtil::trim($location));
		
		if(isset($this->cache_search[$lookupstring])) {
			return $this->cache_search[$lookupstring];
		}
		
		$url = "http://maps.google.com/maps/geo?q=".$lookupstring."&output=csv";
		
		require_once(WCF_DIR.'lib/util/FileUtil.class.php');
		$res = array();
		try {
			$tmp = FileUtil::downloadFileFromHttp($url, 'gmap.search');
			foreach(file($tmp) as $row) {
				if (preg_match('/^200,[^,]+,([^,]+),([^,]+)$/', $row, $hits)) {
					$res = array(
						'lat' => trim($hits[1]),
						'lon' => trim($hits[2])
					);
					break;
				}
			}
			@unlink($tmp);
		} catch(Exception $e) {
			error_log($e->getMessage());
		}

		return $this->cache_search[$lookupstring] = $res;
	}
}
