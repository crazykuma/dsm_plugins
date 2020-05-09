#!/usr/bin/php
<?php

require_once(dirname(__FILE__) . '/../search.inc.php');
require_once(dirname(__FILE__) . '/dbmgr/synomovie.php');
require_once(dirname(__FILE__) . '/dbmgr/synotvshow.php');

define('PLUGINID', 			'com.synology.Synovideodb');

$DEFAULT_TYPE = 'movie';
$DEFAULT_LANG = 'enu';

$SUPPORTED_TYPE = array('movie', 'tvshow_episode');
$SUPPORTED_PROPERTIES = array('title');

function ConvertToAPILang($lang)
{
	static $map = array(
		'enu' => 'enu',
		'cht' => 'cht'
	);

	$ret = isset($map[$lang]) ? $map[$lang] : NULL;
	return $ret;
}

function Process($input, $lang, $type, $limit, $search_properties, $allowguess)
{
	global $DATA_TEMPLATE;
	if( isset($input['javdb']) )
		return array();
	//Init
	$title 	= $input['title'];
	$year 	= ParseYear($input['original_available']);
	$lang 	= ConvertToAPILang($lang);
	$season  = $input['season'];
	$episode = $input['episode'];
	if (!$lang) {
		return array();
	}
	
	//year
	if (isset($input['extra']) && count($input['extra']) > 0) {
		$pluginid = array_shift($input['extra']);
		if (!empty($pluginid['tvshow']['original_available'])) {
			$year = ParseYear($pluginid['tvshow']['original_available']);
		}
	}

	//Set
	$cache_dir = GetPluginDataDirectory(PLUGINID);

	if ("movie" == $type) {
		//Get videodb
		$videodb = new Synomovie();
		$videodb->Init(PLUGINID, $cache_dir);

		//Search
		$query_data = array();
		$titles = GetGuessingList($title, $allowguess);
		foreach ($titles as $query) {
			if (empty($query)) {
				continue;
			}
			if ($year) {
				$query = "{$query} {$year}";
			}
			$query_data = $videodb->Query($query, $lang, $limit);
			if (0 < count($query_data)) {
				break;
			}
		}
		//Get metadata
		return $videodb->GetMovieMetadata($query_data, $DATA_TEMPLATE);
	} else if ("tvshow_episode" == $type) {
		//Get videodb
		$videodb = new Synotvshow();
		$videodb->Init(PLUGINID, $cache_dir);

		//Search
		$query_data = array();
		$titles = GetGuessingList($title, $allowguess);
		foreach ($titles as $query) {
			if (empty($query)) {
				continue;
			}
			if ($year) {
				$query = "{$query} {$year}";
			}
			$query_data = $videodb->Query($query, $lang, $limit);
			if (0 < count($query_data)) {
				break;
			}
		}

		//Get metadata
		return $videodb->GetTvshowEpisodeMetadata($query_data, $lang, $season, $episode, $DATA_TEMPLATE);
	}
}

PluginRun('Process');

?>
