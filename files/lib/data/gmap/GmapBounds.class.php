<?php

/**
 * gets several positions and builds the bounding box
 *
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GmapBounds {
	public $left = 0, $right = 0, $bottom = 0, $top = 0;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->left = -180;
		$this->right = 180;
		$this->bottom = -180;
		$this->top = 180;
	}

	/**
	 * adds Position to group
	 * @param lat -> latitude
	 * @param lng -> longitude
	 */
	public function add($lat, $lng) {
		$lat = floatval($lat);
		$lng = floatval($lng);

		if($this->left == -180 || $lat < $this->left) {
			$this->left = $lat;
		}
		if($this->right == 180 || $lat > $this->right) {
			$this->right = $lat;
		}
		if($this->bottom == -180 || $lng < $this->bottom) {
			$this->bottom = $lng;
		}
		if($this->top == 180 || $lng > $this->top) {
			$this->top = $lng;
		}
	}

	/*
	 * Overwrites the toString method
	 */
	public function __toString() {
		return implode(",",array($this->left,$this->top,$this->right,$this->bottom));
	}
}
?>
