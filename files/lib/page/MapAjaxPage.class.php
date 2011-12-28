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
		
		// pick action
		$this->idx = isset($_GET['idx']) ? intval($_GET['idx']) : 0;
	}

        /**
         * @see Page::readData()
         */
	public function readData() {
		parent::readData();
		
		if (WCF::getUser()->getPermission('user.profile.gmap.canViewUsers')) {
			$this->readUserData();
		}
	}

	/**
	 * read user data
	 */
	public function readUserData() {

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

		if($this->action == 'initialize') {
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
			$ids = $cluster->getMarkers($markers, $this->idx);
			if($ids === null) {
				$this->datapoints = array();	
				$this->datapoints[] = array(
					0,
					'data n/a',
					''
				);
			} else {
				$this->datapoints = $this->getUsers($ids);
			}
		} else {
			$this->datapoints = $cluster->getMarkers($markers);
		}
        }

	/**
	 * Returns a list of users.
	 * 
	 * @param 	string				$userIDs
	 * @return 	array<UserProfile>		users
	 */
	public function getUsers(array $userIDs) {
		require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');

		$users = array();
		$sql = "SELECT		avatar_table.*,
					user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_avatar avatar_table
			ON		(avatar_table.avatarID = user_table.avatarID)
			WHERE		user_table.userID IN (".implode(',', $userIDs).")
			ORDER BY	username";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {

			$avatar = '';
			$user = new UserProfile(null, $row);
			if($user->getAvatar()) {
				$user->getAvatar()->setMaxSize(24, 24);
				$avatar = $user->getAvatar()->getURL();
			}
			
			$username = $user->username;
			
			// json encode can only use utf-8
			if(CHARSET != 'UTF-8') {
				$username = StringUtil::convertEncoding(CHARSET, 'UTF-8', $username);
			}
					
			$users[] = array(
				intval($user->userID),
				$username,
				$avatar
			);
		}

		return $users;
	}

        /**
         * @see Page::show()
         */
        public function show() {

		// skip
		if(!MODULE_GMAP) {
			throw new IllegalLinkException();
		}
        
		parent::show();
		
		// json encode can only use utf-8, thats why we converted all data before
		$str = json_encode($this->datapoints);

		@header('Content-Type: application/json; charset='.CHARSET);
		if(CHARSET != 'UTF-8') {
			echo StringUtil::convertEncoding('UTF-8', CHARSET, $str);
		} else {
			echo $str;
		}
        }
}
?>
