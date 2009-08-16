<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Returns the abstract page for for the Gooogle Map
 *
 * @package     de.gmap.wcf.data.page
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapPage extends AbstractPage {

	// has to be set, otherwise the event StructuredTemplate::shouldDisplay is not called and seo links are not rewritten
	public $templateName = 'MapPage'; 

	private $type;
	private $groups=array();
	private $users=array();
	private $default_type='cities';
	public $ajax_filter=array('team'=>'false','online'=>'false'); //active filters from the beginning
	
	// map key default setting
	public $map_key = MAP_API;

        /**
         * @see Page::readParameters()
         */
        public function readParameters() {
                parent::readParameters();
                
                // do not make any suggestions here - its easy to manipulate, so the inputs are checked in MapAjax
                $this->type = isset($_GET['type']) ? $_GET['type'] : $this->default_type;

                if(isset($_GET['users'])) { // for distance
			$this->users = ArrayUtil::toIntegerArray(explode(',', $_GET['users']));
		}
		
		if(isset($_GET['team'])) {
			$this->ajax_filter['team'] = 'true';
		}
		
		if(isset($_GET['online'])) {
			$this->ajax_filter['online'] = 'true';
		}
        }
        
        /**
         * @see Page::readData()
         */
        public function readData() {
                parent::readData();
                EventHandler::fireAction($this, 'construct'); // overwrite api key?
                
		// sql query to fetch groups
		// left join to see if user is member
		$sql = "SELECT          g.groupID, 
					g.groupName,
					(NOT ISNULL(ug.groupID)) AS isMember
			FROM            wcf".WCF_N."_group g
			LEFT JOIN	wcf".WCF_N."_user_to_groups ug
			ON		ug.groupID = g.groupID
			AND		ug.userID = ".intval(WCF::getUser()->userID)."
			WHERE           groupID > 2
			AND             (
					SELECT 	COUNT( userID )
					FROM 	wcf".WCF_N."_user_to_groups user_to_groups
					WHERE 	wcf".WCF_N."_group.groupID = user_to_groups.groupID
					) > 0;";

		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$key = $row['groupID'];
			$val = addslashes(StringUtil::encodeHTML($row['groupName']));
			$this->groups[] = array($key, $val, $row['isMember']);
		}
        }

         /**
         * @see Page::assignVariables()
         */
        public function assignVariables() {
                parent::assignVariables();
                
		if(MAP_BIGMAP_GROUPFILTER != "n" && defined('MAP_GROUP_ONLINE') && MAP_GROUP_ONLINE) {
			$lang = addslashes(WCF::getLanguage()->get('wcf.map.filter_online'));
			WCF::getTPL()->append('additionalFilters', 
				"ajax_filter.online = {$this->ajax_filter['online']};
				gAddGroup(function(e) { gFilterOption('online', this.checked); }, '{$lang}', ajax_filter.online);");
		}

		WCF::getTPL()->assign(array(
			'gmap_type' => $this->type,
			'gmap_groups' => $this->groups,
			'gmap_users' => $this->users,
			'gmap_admin' => WCF::getUser()->getPermission('user.map.canAdd'),
			'gmap_map_key' => $this->map_key,
			'allowSpidersToIndexThisPage' => true
		));
		
		WCF::getTPL()->append('specialStyles', '<link rel="stylesheet" type="text/css" media="screen" href="'.RELATIVE_WCF_DIR.'style/g-map.css" />');
        }

	/**
	 * @see Page::show()
	 */
	public function show() {
		// necessary? see ticket #30
		@header('Content-Type: text/html; charset='.CHARSET);

		// set active header menu item
		require_once(WCF_DIR.'lib/page/util/menu/HeaderMenu.class.php');
		HeaderMenu::setActiveMenuItem('wcf.header.menu.map');

		// has to be called after setting active menu item #49
		parent::show();
	}
}
?>
