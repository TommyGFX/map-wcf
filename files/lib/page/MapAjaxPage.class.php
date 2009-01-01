<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

// bounding box import
require_once(WCF_DIR.'lib/util/BoundsUtil.class.php');

/**
 * Returns the AJAX Content for the Gooogle Map
 *
 * @package     de.gmap.wcf.data.page
 * @author      Michael Senkler, Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapAjaxPage extends AbstractPage {
	// params
	private $type;
	private $filter = array();
	private $users = array();

	// errorcodes
	private $errors = array();
	
	private $markers = false;
	private $boundsUtil;
	private $pins = 0;
	private $total = 0;
	private $dist;
	private $positions = array();	
	private $coord, $city, $zip, $enable, $max; // column names
	
	// extensions hell
	private $extColumns = array();
	private $extJoins = array();
	private $extConditions = array();
	private $extGroups = array();
	private $extOrders = array();
	
	/**
	 * checks the permission
	 */
	private function checkPermission() {
		return WCF::getUser()->getPermission('user.map.canView') || WCF::getUser()->getPermission('user.map.canViewPersonal');
	}
	
	/**
	 * appends the filter
	 * @param idx
	 * @param array
	 */
	public function appendFilter($idx, $array) {
		if(isset($this->filter[$idx])) {
			$this->filter[$idx] = array_merge($this->filter[$idx], $array);
		} else {
			$this->filter[$idx] = $array;
		}
	}

        /**
         * @see Page::readParameters()
         */
        public function readParameters() {
		parent::readParameters();

		$this->type = isset($_GET['type']) ? $_GET['type'] : null;
		
		if(isset($_GET['users'])) { // for distance
			$this->users = ArrayUtil::toIntegerArray(explode(',', $_GET['users']));
		}

		if(isset($_GET['groups'])) {
			$this->filter['groups'] = ArrayUtil::toIntegerArray(explode(',', $_GET['groups']));
		}
		
		if(isset($_GET['markers'])) {
			$this->markers = true;
		}
		
		if(isset($_GET['online'])) {
			require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnlineList.class.php');
			$usersOnlineList = new UsersOnlineList('', false);
			$usersOnlineList->getUsersOnline();
			$this->filter['users'] = array_map(create_function('$a','return $a["userID"];'), $usersOnlineList->usersOnline);

			// append the own user to the list
			if(is_numeric(WCF::getUser()->userID) && !in_array(WCF::getUser()->userID, $this->filter['users'])) {
				$this->filter['users'][] = WCF::getUser()->userID;
			}
		}
        }
        
	/**
	 * adds an extension
	 * @param type
	 * @param columns
	 * @param joins
	 * @param conditions
	 * @param groups
	 * @param orders
	 */
	public function addExtension($type, $columns=array(), $joins=array(), $conditions=array(), $groups=array(), $orders=array()) {
		$this->extColumns[$type] = array_key_exists($type, $this->extColumns) ? array_merge($this->extColumns[$type], $columns) : $columns;
		$this->extJoins[$type] = array_key_exists($type, $this->extJoins) ? array_merge($this->extJoins[$type], $joins) : $joins;
		$this->extConditions[$type] = array_key_exists($type, $this->extConditions) ? array_merge($this->extConditions[$type], $conditions) : $conditions;
		$this->extGroups[$type] = array_key_exists($type, $this->extGroups) ? array_merge($this->extGroups[$type], $groups) : $groups;
		$this->extOrders[$type] = array_key_exists($type, $this->extOrders) ? array_merge($this->extOrders[$type], $orders) : $orders;
	}
        
        /**
         *
         * @param pt1 -> point1 in format array(lat, lng)
         * @param pt2 -> point2 in format array(lat, lng)
         */
        private function getDistance($pt1, $pt2) {
        	$lat1 = $pt1[0];
        	$lng1 = $pt1[1];
        	$lat2 = $pt2[0];
        	$lng2 = $pt2[1];

		return round(acos((sin($lat1) * sin($lat2)) + (cos($lat1) * cos($lat2) * cos($lng1 - $lng2))) * 6380 / 100 * 1.609344,2);
	}

	/**
	 * cleans and optimizes data for individual encoding
	 * @param value -> single value to optimize
	 */
	private function optimizeSingle($value) {
		if(CHARSET != 'UTF-8') {
			$value = StringUtil::convertEncoding(CHARSET, 'UTF-8', $value);
		}
		$value = str_replace(array("&","<",">","\""), array("&amp;","&lt;","&gt;","'"), $value);

		return $value;
	}

	/**
	 * apply charset optimization on any item, and merges the to arrays to an identic length
	 * @param keys -> keys
	 * @param values -> values
	 * @return -> userlist (key=>val)
	 */
	private function optimizeAll($keys, $values) {
		$values = array_map(array($this, 'optimizeSingle'), $values);
			
		// if there are more names than ids this shows, that GROUP_CONCAT comes to its limit with 1024 Bytes
		$count_k = count($keys);
		$count_v = count($values);
		
		if($count_k != $count_v) {
			// the last user will be useless
			array_splice($keys, $count_v-1);
			array_splice($values, $count_v-1);
		}

		$userlist = array_combine($keys, $values);

		// sort by username - ignore casesensitive
		natcasesort($userlist);
		
		return $userlist;
	}
        
        /**
         * fetchs columns
         */
        private function fetchColumns() {
		$this->coord = "userOption".User::getUserOptionID('map_coord');
		$this->city = "userOption".User::getUserOptionID('map_city');
		$this->zip = "userOption".User::getUserOptionID('map_zip');
		$this->enable = "userOption".User::getUserOptionID('map_enable');
		$this->max = MAP_BIGMAP_MAX_ENTRYS;
        }
        
	/**
	 * apply filters on given sql query
	 * @param sql -> sql query
	 * @param filters -> array
	 */
	private function applyFilters($sql, $filters) {
		foreach($filters as $key => $val) {
			$string = '';

			switch($key) {
				case 'groups':
					$string = "
						AND 1 < (	
							SELECT 		COUNT(user_to_groups.userID) 
							FROM 		wcf".WCF_N."_user_to_groups user_to_groups 
							WHERE 		user_to_groups.userID = user.userID 
							AND 		user_to_groups.groupID NOT IN (".implode(',',$val).")
						)";
				break;
				case 'users':
					$string = "
						AND user.userID IN (".implode(',',$val).") ";
				break;
			}
			
			$sql = preg_replace("/(AND.+\n)/", "$1{$string}\n", $sql, 1);
		}
		return $sql;
	}
        
        /**
         * read data by posts
         */
        private function readDataByPosts() {
		// sql query		
		//
        }
        
        /**
         *
         */
        private function cachedDataByCities() {
		WCF::getCache()->addResource('cities', WCF_DIR.'cache/cache.map-ajax.php', WCF_DIR.'lib/system/cache/CacheBuilderMapAjax.class.php');
		$this->positions = WCF::getCache()->get('cities');
	}

	/**
	 *
	 */
	private function pluginColumns($type) {
		if(array_key_exists($type, $this->extColumns) && count($this->extColumns[$type]) > 0) {
			return implode(",", $this->extColumns[$type]);
		}
	}

	/**
	 *
	 */
	private function pluginJoins($type) {
		if(array_key_exists($type, $this->extJoins) && count($this->extJoins[$type]) > 0) {
			return implode(",", $this->extJoins[$type]);
		}
	}

	/**
	 * @param trueFalse
	 */
	private function pluginConditions($type,$trueFalse) {
		if(array_key_exists($type, $this->extConditions) && count($this->extConditions[$type]) > 0) {
			return ($trueFalse?' WHERE ':' AND ').implode(" AND ", $this->extConditions[$type]);
		}
	}

	/**
	 * @param trueFalse
	 */
	private function pluginGroups($type,$trueFalse) {
		if(array_key_exists($type, $this->extGroups) && count($this->extGroups[$type]) > 0) {
			return ($trueFalse?' GROUP BY ':' , ').implode(",", $this->extGroups[$type]);
		}
	}

	/**
	 * @param trueFalse
	 */
	private function pluginOrders($type,$trueFalse) {
		if(array_key_exists($type, $this->extOrders) && count($this->extOrders[$type]) > 0) {
			return ($trueFalse?' ORDER BY ':' , ').implode(",", $this->extOrders[$type]);
		}
	}

	/**
	 * read data by users
	 */
	private function readDataByCities() {
		if(!WCF::getUser()->getPermission('user.map.canView'))
			return;

		$max = $this->max - $this->pins;
        	
		// sql query		
		$sql = "SELECT		COUNT(*) AS c,
					GROUP_CONCAT(REPLACE(user.username,' ','&nbsp;') SEPARATOR ' ') AS mixNames, 
					GROUP_CONCAT(user.userID SEPARATOR ' ') AS mixIDs, 
					user_option_value.{$this->coord} AS pos,
					CONCAT(user_option_value.{$this->city}, ' (', COUNT(*), ')') AS head
					".$this->pluginColumns('cities')."
			FROM		wcf".WCF_N."_user_option_value user_option_value 
					NATURAL JOIN  wcf".WCF_N."_user user 
					".$this->pluginJoins('cities')."
			WHERE    	{$this->enable} = 1
			AND		{$this->coord} != '0,0' 
			AND      	{$this->coord} != ''
					".$this->pluginConditions('cities',false)." 
			GROUP BY 	{$this->coord}
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
         * reads personal markers which are editable by user
         */
        private function readDataAdmin() {
		if(!WCF::getUser()->getPermission('user.map.canAdd') || !WCF::getUser()->getPermission('user.map.canViewPersonal'))
			return;

		// sql query
		$sql = "SELECT		mapID,
					X(pt) AS lng,
					Y(pt) AS lat,
					mapTitle AS head,
					mapInfo
					".$this->pluginColumns('admin')."
			FROM		wcf".WCF_N."_gmap user 
					".$this->pluginJoins('admin')."
					".$this->pluginConditions('admin',true)."
					".$this->pluginGroups('admin',true)."
					".$this->pluginOrders('admin',true);
			
		// restrict to own user
		if(!WCF::getUser()->getPermission('user.map.canUpdateNonPersonal')) {
			$sql .= "	WHERE userID = ".WCF::getUser()->userID;
		}

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
					'user'	=> array($row['mapID']=>"<![CDATA[".$row['mapInfo']."]]>")
				);
				
			$this->boundsUtil->add($pos[0], $pos[1]);

			// increase counters
			$this->total++;
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
					user_option_value.{$this->coord} AS coord,
					user_option_value.{$this->city} AS head
					".$this->pluginColumns('distance')."
			FROM		wcf".WCF_N."_user_option_value user_option_value 
					NATURAL JOIN  wcf".WCF_N."_user user
					".$this->pluginJoins('distance')."
			WHERE    	{$this->enable} = 1
			AND	    	{$this->coord} != '0,0' 
			AND      	{$this->coord} != '' 
			AND		user.userID IN (".implode(",", $this->users).") 
					".$this->pluginConditions('distance',empty($this->users))."
					".$this->pluginGroups('distance',true)."
					".$this->pluginOrders('distance',true);

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
			$this->dist = $this->getDistance($this->positions[0]['pos'], $this->positions[1]['pos']);
		}
        }

        /**
         * @see Page::readData()
         */
        public function readData() {
		parent::readData();
		
		// bounding box util
		$this->boundsUtil = new BoundsUtil();

		// check permission and abort if there are ABSOLUTLY NO rights
		if(!$this->checkPermission()) {
			$this->errors[] = 0; // PERMISSION_DENIED
			return;
		}

		// fetch column names
                $this->fetchColumns();

                // read positions
                switch($this->type) {
			case 'posts':
				$this->readDataPersonal();
				$this->readDataByPosts();
			break;
			case 'cities':
				$this->readDataPersonal();
				$this->readDataByCities();
			break;
			case 'admin':
				$this->readDataAdmin();
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
		@header('Content-Type: application/xml; charset=UTF-8');
		$tpl_user = '<u id="%d">%s</u>';
		$tpl_pos = '<p lat="%f" lng="%f" h="%s">';

		print "<gmap>
			<!--User Permissions: ".WCF::getUser()->getPermission('user.map.canView').",".WCF::getUser()->getPermission('user.map.canViewPersonal')."//-->
			<total>{$this->total}</total>
			<pins>{$this->pins}</pins>
			<bounds>".$this->boundsUtil->__toString()."</bounds>
			".(!empty($this->dist) ? "<dist>".$this->dist."</dist>" : "")."
			".(count($this->errors) > 0 ? "<errors>".implode(";",$this->errors)."</errors>" : "")."
			<positions>";

		foreach($this->positions as $p) {
			//print position
			printf($tpl_pos, $p['pos'][0], $p['pos'][1], $p['head']);

			//print unters
			foreach($p['user'] as $key => $value) {
				printf($tpl_user, $key, $value);
			}

			print '</p>';
		}

		print "         </positions>
		</gmap>";
        }
}
?>
