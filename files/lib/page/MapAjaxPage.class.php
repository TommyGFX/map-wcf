<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

// map imports
require_once(WCF_DIR.'lib/data/gmap/GmapCluster.class.php');

/**
 * Returns the AJAX Content for the Gooogle Map
 *
 * @package     de.gmap.wcf.data.page
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapAjaxPage extends AbstractPage {
	protected $zoom = 0;
	protected $distance = 35;
	protected $bounds= array();
	protected $initialized = false;
	
	protected $markers = array();
	
        /**
         * @see Page::readData()
         */
        public function readParameters() {
		parent::readParameters();
		
		$this->zoom = max(min(21, isset($_GET['zoom']) ? $_GET['zoom'] : 0), 0);
		
		// ((50.08930948264218, 10.298652648925781), (50.14434619645057, 10.506362915039062))
		if(isset($_GET['bounds'])) {
			if(preg_match('/^\(\((-?\d+\.?\d*), (-?\d+\.?\d*)\), \((-?\d+\.?\d*), (-?\d+\.?\d*)\)\)$/', $_GET['bounds'], $match)) {
				$this->bounds = array(
					array(
						'lat' => $match[1],
						'lon' => $match[2]
					),
					array(
						'lat' => $match[3],
						'lon' => $match[4]
					),
				);

				// extra 30%
				// TODO: let javascript control the bounding box
				$this->bounds[0]['lat'] -= abs($this->bounds[0]['lat'] - $this->bounds[1]['lat']) * 0.3;
				$this->bounds[1]['lat'] += abs($this->bounds[0]['lat'] - $this->bounds[1]['lat']) * 0.3;
				$this->bounds[0]['lon'] -= abs($this->bounds[0]['lon'] - $this->bounds[1]['lon']) * 0.3;
				$this->bounds[1]['lon'] += abs($this->bounds[0]['lon'] - $this->bounds[1]['lon']) * 0.3;
			}
		}
		
		// just get bounds
		$this->initialized = isset($_GET['initialized']) && $_GET['initialized'];
		
		// load content
		$this->content = isset($_GET['content']) && $_GET['content'];
	}
	
        /**
         * @see Page::readData()
         */
        public function readData() {
		parent::readData();
		
		$markers = array();

		$sql = 'SELECT		X(pt) AS lon,
					Y(pt) AS lat
			FROM		wcf'.WCF_N.'_gmap_user
			WHERE		1';
			
		if($this->bounds) {
			$sql .= ' AND X(pt) BETWEEN '.floatval($this->bounds[0]['lon']).' AND '.floatval($this->bounds[1]['lon']).' ';
			$sql .= ' AND Y(pt) BETWEEN '.floatval($this->bounds[0]['lat']).' AND '.floatval($this->bounds[1]['lat']).' ';
		}
			
		if(!$this->initialized) {
			$sql = 'SELECT	AVG(lon) AS lon,
					AVG(lat) AS lat
				FROM (
					'.$sql.'
				) x';
		}

		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$markers[] = $row;
		}
		
		$cluster = new GmapCluster($this->distance, $this->zoom);
		$this->markers = $cluster->getMarkers($markers);
        }

        /**
         * @see Page::show()
         */
        public function show() {
		parent::show();

		echo json_encode($this->markers);
        }
}
?>
