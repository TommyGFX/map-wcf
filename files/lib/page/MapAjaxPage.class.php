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
	protected $zoom;
	protected $distance = 30;
	
	protected $markers = array();
	
        /**
         * @see Page::readData()
         */
        public function readParameters() {
		parent::readParameters();
		
		$this->zoom = min(max(21, $_GET['zoom']), 0);
	}
	
        /**
         * @see Page::readData()
         */
        public function readData() {
		parent::readData();
		
		$markers = array();

		$sql = 'SELECT		X(pt) AS lon,
					Y(pt) AS lat
			FROM		wcf'.WCF_N.'_gmap_user';

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
