#!/usr/bin/php
<?php
header("Content-Type: text/html;charset=utf-8");
require_once(dirname(__FILE__) . '/../util_themoviedb.php');
require_once(dirname(__FILE__) . '/../search.inc.php');

$SUPPORTED_TYPE = array('movie');
$SUPPORTED_PROPERTIES = array('title');


//=========================================================
// javdb begin
//=========================================================
function GetMovieInfoJavDB($movie_data, $data ,$list_data)
{
	if( !isset($movie_data->aka) ) $movie_data->aka = array();
	$data['title']				 	= $list_data['title'];
	$data['tagline'] 				= $list_data['sub_title'];
	$data['original_available'] 	= $movie_data->release_date;
	$data['summary'] 				= $movie_data->summary;
	
	//extra
	$data['extra'] = array();
	$data['extra'][PLUGINID] = array('reference' => array());
	$data['extra'][PLUGINID]['reference']['imdb'] = $movie_data->id;
	$data['javdb'] = true;
	$data['certificate']=[
		"USA"=>"PG-18",
	];
	if (isset($list_data['poster'])) {
		 $data['extra'][PLUGINID]['poster'] = array($list_data['poster']);
	}
	if (isset($movie_data->backdrop)) {
		 $data['extra'][PLUGINID]['backdrop'] = array($movie_data->backdrop);
	}
	if ((float)$movie_data->vote_average) {
		$data['extra'][PLUGINID]['rating'] = array('themoviedb' => (float)$movie_data->vote_average);
	}
	if (isset($movie_data->belongs_to_collection)) {
		 $data['extra'][PLUGINID]['collection_id'] = array('themoviedb' => $movie_data->belongs_to_collection->id);
	}
	
	// genre
	if( isset($movie_data->genres) ){
		foreach ($movie_data->genres as $item) {
			if (!in_array($item, $data['genre'])) {
				array_push($data['genre'], $item);
			}
		}
	}
	// actor
	if( isset($movie_data->actors) ){
		foreach ($movie_data->actors as $item) {
			if (!in_array($item, $data['actor'])) {
				array_push($data['actor'], $item);
			}
		}
	}
	
	// director
	if( isset($movie_data->directors) ){
		foreach ($movie_data->directors as $item) {
			if (!in_array($item, $data['director'])) {
				array_push($data['director'], $item);
			}
		}
	}
	
	// writer
	if( isset($movie_data->writers) ){
		foreach ($movie_data->writers as $item) {
			if (!in_array($item, $data['writer'])) {
				array_push($data['writer'], $item);
			}
		}
	}
	// error_log('getMovieInfo\n', 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
	// error_log(print_r( $data, true), 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
    return $data;
}

/**
 * @brief get metadata for multiple movies
 * @param $query_data [in] a array contains multiple movie item
 * @param $lang [in] a language
 * @return [out] a result array
 */
function GetMetadataJavDB($query_data, $lang)
{
	global $DATA_TEMPLATE;

	//Foreach query result
	$result = array();

	foreach($query_data as $item) {
        //Copy template
		$data = $DATA_TEMPLATE;
		
		//Get movie
		$movie_data = json_decode( shell_exec("python /var/packages/VideoStation/target/plugins/syno_themoviedb/data.py " . $item['id']) );
		if (!$movie_data) {
			continue;
		}
		$data = GetMovieInfoJavDB($movie_data, $data,$item);
		
		//Append to result
		$result[] = $data;
	}

	return $result;
}

function ProcessJavDB($input, $lang, $type, $limit, $search_properties, $allowguess, $id)
{
	$title 	= $input['title'];
	if (!$lang) {
		return array();
	}
	
	if(strtoupper(substr($title,-1)) == "C" ){
		$title = trim(substr($title,0,-1));
	}
	if(substr($title,-1)=="-"){
		$title = trim(substr($title,0,-1));
	}
	//Search
	$query_data = array();
	// error_log("search_start-----------\n", 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
	$query_data = json_decode( shell_exec("python /var/packages/VideoStation/target/plugins/syno_themoviedb/list.py ". $title), true );
	// error_log("search_result：\n", 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
	// error_log(print_r($query_data,true), 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");

	//Get metadata
	return GetMetadataJavDB($query_data['data'], $lang);
}
//=========================================================
// javdb end
//=========================================================


//=========================================================
// tmdb begin
//=========================================================
function GetMovieInfoTMDB($movie_data, $data)
{
    $data['title']				 	= $movie_data->title;
	$data['original_title']			= $movie_data->original_title;
    $data['tagline'] 				= $movie_data->tagline;
    $data['original_available'] 	= $movie_data->release_date;
	$data['summary'] 				= $movie_data->overview;

	foreach ($movie_data->genres as $item) {
		if (!in_array($item->name, $data['genre'])) {
			array_push($data['genre'], $item->name);
		}
    }

	//extra
	$data['extra'] = array();
	$data['extra'][PLUGINID] = array('reference' => array());
	$data['extra'][PLUGINID]['reference']['themoviedb'] = $movie_data->id;
	if (isset($movie_data->imdb_id)) {
		 $data['extra'][PLUGINID]['reference']['imdb'] = $movie_data->imdb_id;
	}
	if ((float)$movie_data->vote_average) {
		$data['extra'][PLUGINID]['rating'] = array('themoviedb' => (float)$movie_data->vote_average);
	}
	if (isset($movie_data->poster_path)) {
		 $data['extra'][PLUGINID]['poster'] = array(BANNER_URL . $movie_data->poster_path);
	}
	if (isset($movie_data->backdrop_path)) {
		 $data['extra'][PLUGINID]['backdrop'] = array(BACKDROUP_URL . $movie_data->backdrop_path);
	}
	if (isset($movie_data->belongs_to_collection)) {
		 $data['extra'][PLUGINID]['collection_id'] = array('themoviedb' => $movie_data->belongs_to_collection->id);
	}

    return $data;
}

function GetCastInfoTMDB($cast_data, $data)
{
    // actor
	foreach ($cast_data->cast as $item) {
		if (!in_array($item->name, $data['actor'])) {
			array_push($data['actor'], $item->name);
		}
    }

    // director & writer
	foreach ($cast_data->crew as $item) {
		if (strcasecmp($item->department, 'Directing') == 0) {
			if (!in_array($item->name, $data['director'])) {
				array_push($data['director'], $item->name);
			}
        }
		if (strcasecmp($item->department, 'Writing') == 0) {
			if (!in_array($item->name, $data['writer'])) {
				array_push($data['writer'], $item->name);
			}
        }
    }

    return $data;
}

function GetCertificateInfoTMDB($releases_data, $data)
{
	$certificate = array();
	foreach ($releases_data->countries as $item) {
		if ('' === $item->certification) {
			continue;
		}
		$name = strcasecmp($item->iso_3166_1, 'us') == 0 ? 'USA' : $item->iso_3166_1;
		$certificate[$name] = $item->certification;
	}
	$data['certificate'] = $certificate;
    return $data;
}

/**
 * @brief get metadata for multiple movies
 * @param $query_data [in] a array contains multiple movie item
 * @param $lang [in] a language
 * @return [out] a result array
 */
function GetMetadataTMDB($query_data, $lang)
{
	global $DATA_TEMPLATE;

	//Foreach query result
	$result = array();
	foreach($query_data as $item) {
		//If languages are different, skip it
		if (0 != strcmp($item['lang'], $lang)) {
			continue;
		}

        //Copy template
		$data = $DATA_TEMPLATE;

		//Get movie
		$movie_data = GetRawdata("movie", array('id' => $item['id'], 'lang' => $item['lang']), DEFAULT_EXPIRED_TIME);
		if (!$movie_data) {
			continue;
		}
		$data = GetMovieInfoTMDB($movie_data, $data);

		//Get cast
		$cast_data = GetRawdata("cast", array('id' => $item['id']), DEFAULT_EXPIRED_TIME);
		if ($cast_data) {
            $data = GetCastInfoTMDB($cast_data, $data);
        }

		//Get certificates
		$releases_data = GetRawdata("releases", array('id' => $item['id']), DEFAULT_EXPIRED_TIME);
		if ($releases_data) {
			$data = GetCertificateInfoTMDB($releases_data, $data);
		}

		//Append to result
		$result[] = $data;
	}

	return $result;
}

function ProcessTMDB($input, $lang, $type, $limit, $search_properties, $allowguess, $id)
{
	$title 	= $input['title'];
	$year 	= ParseYear($input['original_available']);
	$lang 	= ConvertToAPILang($lang);
	if (!$lang) {
		return array();
	}

	if (0 < $id) {
		// if haved id, output metadata directly.
		return GetMetadataTMDB(array(array('id' => $id, 'lang' => $lang)), $lang);
	}

	//Search
	$query_data = array();
	$titles = GetGuessingList($title, $allowguess);
	foreach ($titles as $checkTitle) {
		if (empty($checkTitle)) {
			continue;
		}
		$query_data = Query($checkTitle, $year, $lang, $limit);
		if (0 < count($query_data)) {
			break;
		}
	}

	//Get metadata
	return GetMetadataTMDB($query_data, $lang);
}

//=========================================================
// tmdb end
//=========================================================

function Process($input, $lang, $type, $limit, $search_properties, $allowguess, $id)
{
	try{
		if( 'jpn' == $lang ){
			$RET = ProcessJavDB($input, $lang, $type, $limit, $search_properties, $allowguess, $id);
		}else{
			$RET = ProcessTMDB($input, $lang, $type, $limit, $search_properties, $allowguess, $id);
		}
	}catch(Exception $e){
		error_log(print_r($e,true), 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
	}
	error_log(print_r($RET,true), 3, "/var/packages/VideoStation/target/plugins/syno_themoviedb/my-errors.log");
	return $RET;
}

PluginRun('Process');

