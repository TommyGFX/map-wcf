<?php

/**
 * personal objects
 *
 * @author	Torben Brodt
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */
class GmapPersonal {
	
	/**
	 * checks the position count
	 * @return -> returns true if 
	 */
	protected function fetchCount() {
		$sql = "SELECT		COUNT(*) AS c
			FROM		wcf".WCF_N."_gmap
			WHERE    	userID = ".$this->userID;
		$row = WCF::getDB()->getFirstRow($sql);

		$this->count = intval($row['c']);
	}
        
        /**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['action'])) $this->myaction = CHARSET == 'UTF-8' ? $_POST['action'] : StringUtil::convertEncoding("UTF-8", CHARSET, $_POST['action']);
		if (isset($_POST['mapID'])) $this->mapID = intval($_POST['mapID']);
		if (isset($_POST['lat'])) $this->lat = floatval($_POST['lat']);
		if (isset($_POST['lng'])) $this->lng = floatval($_POST['lng']);
		if (isset($_POST['title'])) $this->title = CHARSET == 'UTF-8' ? $_POST['title'] : StringUtil::convertEncoding("UTF-8", CHARSET, $_POST['title']);
		if (isset($_POST['info'])) $this->info = CHARSET == 'UTF-8' ? $_POST['info'] : StringUtil::convertEncoding("UTF-8", CHARSET, $_POST['info']);
		
		if(empty($this->info)) $this->info = ' ';
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->userID = WCF::getUser()->userID;
		
		// check permission and abort if there are no rights
		if(!$this->checkPermission()) {
			$this->errors[] = 0; // PERMISSION_DENIED
			return;
		}
		
		// fetchs marker count
		$this->fetchCount();
	}
	
	/**
	 * adds new marker
	 */
	protected function add() {
		if(empty($this->lng) || empty($this->lat) || empty($this->title))
			return;
			
		// check the number of position entries added by the user
		if($this->count >= WCF::getUser()->getPermission('user.map.canAddCount')) {
			$this->errors[] = -1; // TO MUCH ENTRIES
			return;
		}
		
		$sql = "INSERT INTO	wcf".WCF_N."_gmap
					(userID, pt, mapTitle)
			VALUES		(
					".$this->userID.",
					PointFromText('POINT(".$this->lng." ".$this->lat.")'),
					'".escapeString($this->title)."'
					); ";
		WCF::getDB()->sendQuery($sql);
		$this->mapID = WCF::getDB()->getInsertID();
	}
	
	/**
	 * updates an existing marker
	 */
	protected function update() {
		$query = array();

		if(!empty($this->lng) && !empty($this->lat))
			$query[] = " pt = PointFromText('POINT(".$this->lng." ".$this->lat.")') ";
		if(!empty($this->title))
			$query[] = " mapTitle = '".escapeString($this->title)."' ";
		if(!empty($this->info)) {
			$parser = MessageParser::getInstance();
			$parser->setOutputType('text/html');
			$cache = $parser->parse($this->info, true, false, true, false);
			
			$query[] = " mapInfo = '".escapeString($this->info)."' ";
			$query[] = " mapInfoCache = '".escapeString($cache)."' ";
		}
			
		if(count($query) == 0 || empty($this->mapID))
			return;

		$sql = "UPDATE		wcf".WCF_N."_gmap 
			SET		".implode(",", $query)."
			WHERE		mapID = {$this->mapID} ";
			
		// restrict to own user
		if(!WCF::getUser()->getPermission('user.map.canUpdateNonPersonal')) {
			$sql .= "	AND		userID = ".$this->userID;
		}

		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * adds new marker
	 */
	protected function remove() {
		if(empty($this->mapID))
			return;

		$sql = "DELETE FROM	wcf".WCF_N."_gmap
			WHERE		mapID = {$this->mapID} ";

		// restrict to own user
		if(!WCF::getUser()->getPermission('user.map.canUpdateNonPersonal')) {
			$sql .= "	AND		userID = ".$this->userID;
		}

		WCF::getDB()->sendQuery($sql);
	}
}
