<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * extends the userpage with the map - maybe also add the personal points
 *
 * @package     de.easy-coding.wcf.blog.map
 * @author      Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class MapUserBlogListener implements EventListener {
	protected $location = '';

	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$this->eventObj = $eventObj;
		switch($className) {
			case 'UserBlogEntryAddForm':
			case 'UserBlogEntryEditForm':
				$call = 'form_'.$eventName;
				$this->$call();
			break;
			case 'UserBlogEntryPage':
				$call = 'page_'.$eventName;
				$this->$call();
			break;
		}
	}

	/**
	 * read blog location
	 */
	protected function page_readData() {
		$sql = 'SELECT		X(pt) AS lon,
					Y(pt) AS lat
			FROM		wcf'.WCF_N.'_gmap_blog
			WHERE		blogID = '.intval($this->eventObj->entryID);
		$result = WCF::getDB()->sendQuery($sql);
		$this->coordinate = WCF::getDB()->fetchArray($result);
	}
	
	protected function page_assignVariables() {
		if($this->coordinate) {
			WCF::getTPL()->assign(array(
				'coordinate' => $this->coordinate
			));

			// if user position exists, add map to beginning
			array_unshift($this->eventObj->sidebar->sidebarBoxes, 'userProfileMapSide');
		}
	}

	/**
	 * read form
	 */
	protected function form_readFormParameters() {
		if(count($_POST)) {
			if (isset($_POST['location'])) $this->location = StringUtil::trim($_POST['location']);
		}
	}

	/**
	 * read existing data if no form was sent
	 */
	protected function form_readData() {
		if(!count($_POST) && isset($this->eventObj->entry)) {
			$this->location = $this->eventObj->entry->location;
		}
	}
	
	/**
	 * show form
	 */
	protected function form_assignVariables() {
		WCF::getTPL()->assign(array(
			'location' => $this->location
		));
		WCF::getTPL()->append('additionalInformationFields', WCF::getTPL()->fetch('userBlogEntryAddLocation'));
	}
	
	protected function form_saved() {
		$sql = "UPDATE  wcf".WCF_N."_user_blog
	                SET     location = '".escapeString($this->location)."'
	                WHERE   entryID = ".intval($this->eventObj->entry->entryID);
	        WCF::getDB()->sendQuery($sql);

		$query = $this->location;		
		if($query) {
			require_once(WCF_DIR.'lib/data/gmap/GmapApi.class.php');
			$api = new GmapApi();
			$point = $api->search($query);
			if(!$point) {
				WCF::getTPL()->append('userMessages', '<p class="error">'.WCF::getLanguage()->get('wcf.map.noPosition').'</p>');
			}
		} else {
			$point = false;
		}

		if($point) {
			$sql = "REPLACE INTO	wcf".WCF_N."_gmap_blog
						(blogID, pt)
				VALUES		(".intval($this->eventObj->entry->entryID).",
						PointFromText('POINT(".$point['lon']." ".$point['lat'].")'))";
			WCF::getDB()->sendQuery($sql);
		}

		// drop user location
		else {
			$sql = "DELETE FROM	wcf".WCF_N."_gmap_blog
				WHERE 		blogID = ".intval($this->eventObj->entry->entryID);
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>
