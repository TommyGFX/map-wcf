<?php
/**
 * migration for gmap version 1
 *
 * @author	Torben Brodt
 * @copyright	2010 easy-coding.de
 * @license	GNU General Public License <http://opensource.org/licenses/gpl-3.0.html>
 */

// check if personal markers are in use
$sql = "SELECT	COUNT(*) AS entries
	FROM	wcf".WCF_N."_gmap";
$row = WCF::getDB()->getFirstRow($sql);

// abort
if($row['entries'] > 0) {
	throw new SystemException('Sorry, personal markers are currently not supported by the update version.
		But you can delete the personal markers to update to the current version.');
}

// all columns existing
$columns = array('location', 'map_coord', 'map_street', 'map_city', 'map_country', 'map_zip', 'map_enable');

$optionIDs = array();
foreach($columns as $column) {
	$id = User::getUserOptionID($column);
	if(empty($id)) {
		throw new SystemException('Sorry, update cannot be run, there is a missing field "'.$column.'"');
	}
	$$column = 'userOption'.$id;
	$optionIDs[] = $id;
}

// update useroption to single column layout
$sql = "UPDATE	wcf".WCF_N."_user_option_value
	SET	$location = IF($map_enable = 0, '', CONCAT(
			CONCAT($location, IF($location = '', '', ' ')),
			CONCAT($map_street, IF($map_street = '', '', ' ')),
			CONCAT($map_zip, IF($map_zip = '', '', ' ')),
			CONCAT(IF($map_city=$location,'',$map_city), IF(IF($map_city=$location,'',$map_city) = '', '', ' ')),
			$map_country
		))";
WCF::getDB()->sendQuery($sql);


// move coordinates to binary table
$sql = "INSERT IGNORE INTO
			wcf".WCF_N."_gmap_user
	SELECT		userID,
			PointFromText(CONCAT('POINT(',
				SUBSTRING($map_coord, LOCATE(',', $map_coord) + 1),
				' ',
				SUBSTRING($map_coord, 1, LOCATE(',', $map_coord) - 1),
			')')) AS pt
	FROM		wcf".WCF_N."_user_option_value
	WHERE		$map_coord != '' AND $map_coord != '0,0'";
WCF::getDB()->sendQuery($sql);

// delete all old files from version 1, careful.. do not delete files from active package
$files = array('icon/glob32.png','icon/glob64.png','icon/glob24.png','icon/g-map/marker0000.png','icon/g-map/lineAdd.png','icon/g-map/bmarker0.png','icon/g-map/markerAddM.png','icon/g-map/bmarker.png','icon/g-map/marker00.png','icon/g-map/dialog-error.svg','icon/g-map/bmarker000.png','icon/g-map/bmarker0000.png','icon/g-map/ymarker0.png','icon/g-map/bmarker00.png','icon/g-map/dialog-warning.svg','icon/g-map/loading.gif','icon/g-map/zoom.gif','icon/g-map/dialog-warning.png','icon/g-map/marker000.png','icon/g-map/dialog-error.png','icon/g-map/marker0.png','icon/g-map/Thumbs.db','icon/g-map/marker.png','icon/g-map/gmarker0.png','icon/glob128.png','icon/glob.svg','icon/glob48.png','icon/glob16.png','icon/Thumbs.db','lib/form/MapUserAjaxForm.class.php','lib/util/MapDiscover.class.php','lib/util/BoundsUtil.class.php','lib/system/event/listener/GMapOptionFormListener.class.php','lib/system/cronjob/CoordCronjob.class.php','lib/system/cache/CacheBuilderMapMarker.class.php','lib/system/cache/CacheBuilderMapAjax.class.php','js/labeled_marker.js','js/g-map.js');
foreach($files as $file) {
	@unlink(WCF_DIR.$file);
}

// delete old language variables
$vars = array('wcf.map.forbidden','wcf.map.tomuchentries','wcf.map.clicknow','wcf.map.copyright','wcf.map.copyright_small','wcf.map.counter_user','wcf.map.counter_marker','wcf.map.satellite','wcf.map.hybrid','wcf.map.map','wcf.map.zoom','wcf.map.user_in_bigmap','wcf.map.filter_online','wcf.map.groupfilter_none','wcf.map.groupfilter_inlay','wcf.map.groupfilter_sub','wcf.map.administrateOn','wcf.map.administrateOff','wcf.map.marker','wcf.map.markerAdd','wcf.map.markerUrl','wcf.map.markerTitle','wcf.map.markerInfo','wcf.map.markerSave','wcf.map.markerCancel','wcf.map.markerRemove','wcf.map.markerClickToAdd','wcf.acp.option.category.map','wcf.acp.option.category.map.preview','wcf.acp.option.category.map.description','wcf.acp.option.category.map.general','wcf.acp.option.category.map.general.description','wcf.acp.option.category.map.bigmap','wcf.acp.option.category.map.bigmap.description','wcf.acp.option.category.map.usermap','wcf.acp.option.category.map.usermap.description','wcf.acp.option.category.map.groups','wcf.acp.option.category.map.groups.description','wcf.acp.option.map_api','wcf.acp.option.map_api.description','wcf.acp.option.map_cron_ip','wcf.acp.option.map_cron_ip.description','wcf.acp.option.map_bigmap_center','wcf.acp.option.map_bigmap_center.description','wcf.acp.option.map_bigmap_zoom','wcf.acp.option.map_bigmap_zoom.description','wcf.acp.option.map_bigmap_ctl_navi','wcf.acp.option.map_bigmap_ctl_navi.description','wcf.acp.option.map_bigmap_ctl_type','wcf.acp.option.map_bigmap_ctl_type.description','wcf.acp.option.map_bigmap_ctl_scal','wcf.acp.option.map_bigmap_ctl_scal.description','wcf.acp.option.map_bigmap_ctl_over','wcf.acp.option.map_bigmap_ctl_over.description','wcf.acp.option.map_bigmap_ctl_mousewheelzoom','wcf.acp.option.map_bigmap_ctl_mousewheelzoom.description','wcf.acp.option.map_bigmap_type','wcf.acp.option.map_bigmap_type.description','wcf.acp.option.map_bigmap_groupfilter','wcf.acp.option.map_bigmap_groupfilter.description','wcf.acp.option.map_group_all','wcf.acp.option.map_group_all.description','wcf.acp.option.map_group_team','wcf.acp.option.map_group_team.description','wcf.acp.option.map_group_active','wcf.acp.option.map_group_active.description','wcf.acp.option.map_group_online','wcf.acp.option.map_group_online.description','wcf.acp.option.map_usermap_ctl_navi','wcf.acp.option.map_usermap_ctl_navi.description','wcf.acp.option.map_usermap_ctl_type','wcf.acp.option.map_usermap_ctl_type.description','wcf.acp.option.map_usermap_ctl_scal','wcf.acp.option.map_usermap_ctl_scal.description','wcf.acp.option.map_usermap_ctl_over','wcf.acp.option.map_usermap_ctl_over.description','wcf.acp.option.map_usermap_type','wcf.acp.option.map_usermap_type.description','wcf.acp.option.map_usermap_zoom','wcf.acp.option.map_usermap_zoom.description','wcf.acp.option.map_usermap_width','wcf.acp.option.map_usermap_width.description','wcf.acp.option.map_usermap_height','wcf.acp.option.map_usermap_height.description','wcf.acp.option.map_usermap_show_right','wcf.acp.option.map_usermap_show_right.description','wcf.acp.option.map_usermap_show_center','wcf.acp.option.map_usermap_show_center.description','wcf.acp.option.map_bigmap_max_entrys','wcf.acp.option.map_bigmap_max_entrys.description','wcf.acp.group.option.category.user.map','wcf.acp.group.option.category.user.map.description','wcf.acp.group.option.user.map.canUse','wcf.acp.group.option.user.map.canUse.description','wcf.acp.group.option.user.map.canAdd','wcf.acp.group.option.user.map.canAdd.description','wcf.acp.group.option.user.map.canAddCount','wcf.acp.group.option.user.map.canAddCount.description','wcf.acp.group.option.user.map.canUpdateNonPersonal','wcf.acp.group.option.user.map.canUpdateNonPersonal.description','wcf.acp.group.option.user.map.canViewPersonal','wcf.acp.group.option.user.map.canViewPersonal.description','wcf.acp.group.option.user.map.canView','wcf.acp.group.option.user.map.canView.description','wcf.acp.group.option.user.map.header','wcf.acp.group.option.user.map.header.description','wcf.user.option.category.map','wcf.user.option.map','wcf.user.option.category.profile.map','wcf.user.option.map_enable','wcf.user.option.map_enable.description','wcf.user.option.map_street','wcf.user.option.map_street.description','wcf.user.option.map_zip','wcf.user.option.map_zip.description','wcf.user.option.map_city','wcf.user.option.map_city.description','wcf.user.option.map_country','wcf.user.option.map_country.description');
foreach(array_chunk($vars, 50) as $chunk) {
	$sql = "DELETE FROM	wcf".WCF_N."_language_item
		WHERE		languageItem IN ('".implode("','", $chunk)."')";
	WCF::getDB()->sendQuery($sql);
}

// transfer api key
if(defined('MAP_API') && defined('GMAP_API_KEY')) {
	$newapi = GMAP_API_KEY;
	if(empty($newapi)) {
		$apikey = array();

		$url = PAGE_URL;
		if(preg_match('/http[s]?:\/\/([^\/]+)/', $url, $res)) {
			$url = $res[1];
		}

		$apikey[] = $url.':'.MAP_API;

		// further api keys?
		// ...

		// update
		$sql = "UPDATE	wcf".WCF_N."_option
			SET	optionValue = '".escapeString(implode("\n", $apikey))."'
			WHERE	optionName = 'gmap_api_key'";
		WCF::getDB()->sendQuery($sql);

		require_once(WCF_DIR.'lib/acp/option/Options.class.php');
		Options::resetFile();
		Options::resetCache();
	}
}

// try to delete this file
@unlink(__FILE__);
?>
