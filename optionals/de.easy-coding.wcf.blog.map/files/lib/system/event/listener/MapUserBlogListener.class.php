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

	protected function page_readData() {
		$blogID = $this->eventObj->entryID;

		// read user location
		$sql = 'SELECT		X(pt) AS lon,
					Y(pt) AS lat
			FROM		wcf'.WCF_N.'_gmap_blog
			WHERE		blogID = '.intval($blogID);
		$result = WCF::getDB()->sendQuery($sql);
		$this->coordinate = WCF::getDB()->fetchArray($result);
	}
	
	protected function page_assignVariables() {
		if($this->coordinate) {
			WCF::getTPL()->assign(array(
				'coordinate' => $this->coordinate
			));
			// if user position exists
			WCF::getTPL()->append('additionalBoxes1', WCF::getTPL()->fetch('userProfileMapSide'));
		}
	}

	protected function form_readData() {
		$blogID = $this->eventObj->entryID;

		// read user location
		$sql = 'SELECT		X(pt) AS lon,
					Y(pt) AS lat
			FROM		wcf'.WCF_N.'_gmap_blog
			WHERE		blogID = '.intval($blogID);
		$result = WCF::getDB()->sendQuery($sql);
		$this->coordinate = WCF::getDB()->fetchArray($result);
	}
	
	protected function form_assignVariables() {
		if($this->coordinate) {
			WCF::getTPL()->assign(array(
				'coordinate' => $this->coordinate
			));
			// if user position exists
			WCF::getTPL()->append('additionalBoxes1', WCF::getTPL()->fetch('userProfileMapSide'));
		}
	}
	
	protected function form_saved() {
		$query = trim(implode(' ', $this->location));
		if($query) {
			require_once(WCF_DIR.'lib/data/gmap/GmapApi.class.php');
			$api = new GmapApi();
			$point = $api->search($query);
			if(!$point) {
				WCF::getTPL()->append('<p class="error">'.WCF::getLanguage()->get('wcf.map.noPosition').'</p>');
			}
		} else {
			$point = false;
		}

		if($point) {
			$sql = "REPLACE INTO	wcf".WCF_N."_gmap_blog
						(blogID, pt)
				VALUES		(".intval($this->eventObj->entry->blogID).",
						PointFromText('POINT(".$point['lon']." ".$point['lat'].")'))";
			WCF::getDB()->sendQuery($sql);
		}

		// drop user location
		else {
			$sql = "DELETE FROM	wcf".WCF_N."_gmap_blog
				WHERE 		blogID = ".intval($this->eventObj->entry->blogID);
			WCF::getDB()->sendQuery($sql);
		}
	}
}
?>
