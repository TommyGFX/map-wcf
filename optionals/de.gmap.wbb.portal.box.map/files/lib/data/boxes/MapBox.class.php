<?php

/**
 * Map Box
 *
 * @package     de.gmap.wcf.data.boxes
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapBox {
	protected $mapBoxList = array();

	/**
	 *
	 * @param data
	 * @param boxname
	 */
	public function __construct($data, $boxname = "") {
		$this->getBoxStatus($data);

		$this->mapBoxList['templatename'] = "mapBox";
		$this->mapBoxList['data'] = WBBCore::getCache()->get('box-' . $data['boxID']);
		$this->mapBoxList['boxID'] = $data['boxID'];

		$this->mapBoxList['mapbox'] = array(); //todo

	}

	/**
	 *
	 * @param data
	 */
	protected function getBoxStatus($data) {
		// get box status
		$this->mapBoxList['Status'] = 1;
		if (WBBCore::getUser()->userID) {
			$this->mapBoxList['Status'] = intval(WBBCore::getUser()->mapbox);
		}
		else if (WBBCore::getSession()->getVar('mapbox') !== false) {
			$this->mapBoxList['Status'] = WBBCore::getSession()->getVar('mapbox');
		}
	}

	/**
	 *
	 */
	public function getData() {
		return $this->mapBoxList;
	}

}

?>
