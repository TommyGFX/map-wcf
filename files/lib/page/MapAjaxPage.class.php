<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

// bounding box import
require_once(WCF_DIR.'lib/util/BoundsUtil.class.php');

/**
 * Returns the AJAX Content for the Gooogle Map
 *
 * @package     de.gmap.wcf.data.page
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapAjaxPage extends AbstractPage {

	/**
	 * read data by users
	 */
	private function readDataByCities() {
		if(!WCF::getUser()->getPermission('user.map.canView'))
			return;

		$max = MAP_BIGMAP_MAX_ENTRYS - $this->pins;
        	
		// sql query		
		$sql = "SELECT		COUNT(*) AS c,
					GROUP_CONCAT(REPLACE(user.username,' ','&nbsp;') SEPARATOR ' ') AS mixNames, 
					GROUP_CONCAT(user.userID SEPARATOR ' ') AS mixIDs, 
					coords AS pos,
					CONCAT(location, ' (', COUNT(*), ')') AS head
					".$this->pluginColumns('cities')."
			FROM		wcf".WCF_N."_user user 
					".$this->pluginJoins('cities')."
			WHERE    	
			GROUP BY 	coords
					".$this->pluginGroups('cities',false)."
			ORDER BY 	c DESC
					".$this->pluginOrders('cities',false)." 
					LIMIT {$max};";
					
		// apply filters
		$sql = $this->applyFilters($sql, $this->filter);

		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$keys = explode(' ', $row['mixIDs']);
			$values = explode(' ', $row['mixNames']);
			
			$userlist = $this->optimizeAll($keys, $values);
			
			$this->positions[] = array(
					'pos'	=> explode(',',$row['pos']),
					'head'	=> $this->optimizeSingle($row['head']),
					'user'	=> $userlist
				);

			// increase counters
			$this->total += $row['c'];
			$this->pins++;
		}
        }
        
        /**
         * reads public personal markers
         */
        private function readDataPersonal() {
		if(!$this->markers || !WCF::getUser()->getPermission('user.map.canViewPersonal'))
			return;

        	// abort, if there are no users
		if($this->type == 'personal' && count($this->users) == 0)
			return;

		// sql query		
		$sql = "SELECT		mapID,
					X(pt) AS lng,
					Y(pt) AS lat,
					mapTitle AS head,
					mapInfoCache
			FROM		wcf".WCF_N."_gmap 
					".$this->pluginJoins('personal').
					(!empty($this->users) ? "WHERE userID IN (".implode(",", $this->users).");" : ";").
					$this->pluginConditions('personal',empty($this->users))."
					".$this->pluginGroups('personal',true)."
					".$this->pluginOrders('personal',true);

		// apply filters
		$sql = $this->applyFilters($sql, $this->filter);

		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$pos = array($row['lat'],$row['lng']);
			
			// that's the trick to seperate users from public markers
			$row['mapID'] *= -1;
			
			$this->positions[] = array(
					'pos'	=> $pos,
					'head'	=> $this->optimizeSingle($row['head']),
					'user'	=> array($row['mapID']=>"<![CDATA[".$row['mapInfoCache']."]]>")
				);
				
			$this->boundsUtil->add($pos[0], $pos[1]);

			// increase counters
			$this->total++;
			$this->pins++;
		}
        }
        
        /**
         * reads alien user and own user
         */
        private function readDataDistance() {
		if(!WCF::getUser()->getPermission('user.map.canView'))
			return;

        	// abort, if there are no users
		if(count($this->users) == 0)
			return;

		// append own user
		if(is_numeric(WCF::getUser()->userID) && !in_array(WCF::getUser()->userID, $this->users)) {
			$this->users[] = WCF::getUser()->userID;
		}
        
		// sql query		
		$sql = "SELECT		user.userID, user.username,
					coords AS coord,
					location AS head
					".$this->pluginColumns('distance')."
			FROM		wcf".WCF_N."_user user
					".$this->pluginJoins('distance')."
			WHERE    	user.userID IN (".implode(",", $this->users).") 
					".$this->pluginConditions('distance', false)."
					".$this->pluginGroups('distance', true)."
					".$this->pluginOrders('distance', true);

		// apply filters
		$sql = $this->applyFilters($sql, $this->filter);

		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$keys = array($row['userID']);
			$values = array($row['username']);
			
			$userlist = $this->optimizeAll($keys, $values);
			$pos = explode(',',$row['coord']);
			
			$this->positions[] = array(
					'pos'	=> $pos,
					'head'	=> $this->optimizeSingle($row['head']),
					'user'	=> $userlist
				);
				
			$this->boundsUtil->add($pos[0], $pos[1]);

			// increase counters
			$this->total++;
			$this->pins++;
		}
		
		if(count($this->positions) > 1) {
			$this->dist = MapDiscover::getDistance($this->positions[0]['pos'], $this->positions[1]['pos']);
		}
        }

        /**
         * @see Page::readData()
         */
        public function readData() {
		parent::readData();
		
		// bounding box util
		$this->boundsUtil = new BoundsUtil();

                // read positions
                switch($this->type) {
			case 'cities':
				$this->readDataPersonal();
				$this->readDataByCities();
			break;
			case 'distance':
				$this->readDataPersonal();
				$this->readDataDistance();
			break;
                }
        }

        /**
         * @see Page::show()
         */
        public function show() {
		parent::show();

		// send header
		@header('Content-Type: application/xml; charset='.CHARSET);
		
		foreach($this->positions as $p) {
			
		}
        }
}
?>
