<?php

/**
 * gets several positions and returns a clustered array
 *
 * @author	Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GmapCluster {

	const OFFSET = 268435456;
	const RADIUS = 85445659.4471; /* $self::OFFSET / pi() */
	
	protected $distance;
	protected $zoom;
	
	/**
	 *
	 * @param	$distance	Distance in pixel inside which markers will be clustered.
	 * @param	$zoom		Current map zoom level.
	 */
	public function __construct($distance, $zoom) {
		$this->distance = $distance;
		$this->zoom = $zoom;
	}
	
	protected function lonToX($lon) {
		return round(self::OFFSET + self::RADIUS * $lon * pi() / 180);		
	}

	protected function latToY($lat) {
		return round(self::OFFSET - self::RADIUS * log((1 + sin($lat * pi() / 180)) / (1 - sin($lat * pi() / 180))) / 2);
	}

	protected function pixelDistance($lat1, $lon1, $lat2, $lon2, $zoom) {
		$x1 = $this->lonToX($lon1);
		$y1 = $this->latToY($lat1);

		$x2 = $this->lonToX($lon2);
		$y2 = $this->latToY($lat2);
		
		return sqrt(pow(($x1-$x2),2) + pow(($y1-$y2),2)) >> (21 - $zoom);
	}
	
	/**
	 * Return average center of markers
	 *
	 * @return   object Google_Maps_Coordinate
	 */
	protected function getCluster(array $markers) {
		$count = count($markers);

		/* Calculate average lat and lon of markers. */
		$lat_sum = $lon_sum = 0;
		foreach ($markers as $marker) {
		   $lat_sum += $marker['lat'];
		   $lon_sum += $marker['lon'];
		}
		$lat_avg = $lat_sum / $count;
		$lon_avg = $lon_sum / $count;
		
		return array(
			'count' => $count,
			'lat' => $lat_avg,
			'lon' => $lon_avg
		);
	}

	/**
	 *
	 * @param	$markers	Array of lat and lon locations.
	 */
	public function getMarkers(array $markers) {
		$clustered = array();

		/* Loop until all markers have been compared. */
		while (count($markers)) {
			$marker  = array_pop($markers);
			$cluster = array();

			/* Compare against all markers which are left. */
			foreach ($markers as $key => $target) {
				$pixels = $this->pixelDistance($marker['lat'], $marker['lon'], $target['lat'], $target['lon'], $zoom);

				/* If two markers are closer than given distance remove */
				/* target marker from array and add it to cluster.	  */
				if ($distance > $pixels) {
					unset($markers[$key]);
					$cluster[] = $target;
				}
			}

			/* If a marker has been added to cluster, add also the one  */
			/* we were comparing to and remove the original from array. */
			if (count($cluster) > 0) {
				$cluster[] = $marker;
				$clustered[] = $this->getCluster($cluster);
			} else {
				$clustered[] = $marker;
			}
		}
		return $clustered;
	}
}
