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
	public $action = '';
	protected $zoom = 0;
	protected $distance = 35;
	protected $bounds= array();
	
	protected $datapoints = array();
	
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
			}
		}
		
		// just get bounds
		$this->action = $_GET['action'];
		
		// load content
		$this->content = isset($_GET['content']) && $_GET['content'];
		
		// coordinates
		$this->lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
		$this->lon = isset($_GET['lon']) ? floatval($_GET['lon']) : 0;
	}
	
        /**
         * @see Page::readData()
         */
        public function readData() {
		parent::readData();
		
		$markers = array();

		$sql = 'SELECT		'.($this->action == 'pick' ? 'userID AS id,' : '').'
					X(pt) AS lon,
					Y(pt) AS lat
			FROM		wcf'.WCF_N.'_gmap_user
			WHERE		1';
			
		if($this->bounds) {
			$sql .= ' AND X(pt) BETWEEN '.floatval($this->bounds[0]['lon']).' AND '.floatval($this->bounds[1]['lon']).' ';
			$sql .= ' AND Y(pt) BETWEEN '.floatval($this->bounds[0]['lat']).' AND '.floatval($this->bounds[1]['lat']).' ';
		}
			
		if(!$this->action == 'initialize') {
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
		if($this->action == 'pick') {
			require_once(WCF_DIR.'lib/data/user/User.class.php');
			$ids = $cluster->getIDs($markers, $this->lat, $this->lon);
			$this->datapoints = User::getUsers(implode(",", $ids));
			$this->datapoints = array_map(create_function('$a', 'return array(
				intval($a->userID),
				$a->username
			);'), $this->datapoints);
		} else {
			$this->datapoints = $cluster->getMarkers($markers);
		}
        }

        /**
         * @see Page::show()
         */
        public function show() {
		parent::show();

		echo json_encode($this->datapoints);
        }
}
?>
