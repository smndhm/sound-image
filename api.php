<?php
	
	//TODO: Edit this variable with your serveur script location
	$websiteUrl  = "http://{$_SERVER['SERVER_NAME']}/lab/sound-image/";
	
	/**
	 * @param $url file url
	 * @return mixed file content
	 */
	function curl($url) {
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		$content = curl_exec($c);
		curl_close($c);
		return $content;
	}

	$json = array();
	
	if (isset($_GET['track_id'])) {
		$_GET['id'] = $_GET['track_id'];
	}
	
	if (isset($_GET['id']) && !empty($_GET['id'])) {

		//File ID
		$hash     = "5OUND-1M4G3";
		$filename = md5($hash.$_GET['id']);
		
		//PATHS
		$staticPath = 'static/';
		$coverPath  = $staticPath.'cover/';
		$mp3Path    = $staticPath.'mp3/';
		$videoPath  = $staticPath.'video/';
		$jsonPath   = $staticPath.'json/';
		//FILES
		$coverUrl = $coverPath.$filename.'.jpg';
		$mp3Url   = $mp3Path.$filename.'.mp3';
		$videoUrl = $videoPath.$filename.'.mp4';
		$jsonUrl  = $jsonPath.$filename.'.json';
		//FFmpeg command
		$ffmpegCmd = './ffmpeg/ffmpeg ';
		$ffmpegCmd.= '-loop 1 -i %1$s '; //input .jpg
		$ffmpegCmd.= '-i %2$s '; //input .mp3
		$ffmpegCmd.= '-strict experimental -c:a aac -b:a 128k '; //audio options
		$ffmpegCmd.= '-c:v libx264 -tune stillimage -pix_fmt yuv420p -r 0.033 '; //video options
		$ffmpegCmd.= '-metadata title="%3$s" ';
		$ffmpegCmd.= '-metadata author="%4$s" ';
		$ffmpegCmd.= '-metadata album_artist="%4$s" ';
		$ffmpegCmd.= '-metadata album="%5$s" ';
		$ffmpegCmd.= '-metadata description="Created by Simon Duhem. Using Deezer API & ffmpeg." ';
		$ffmpegCmd.= '-shortest -t 30 -y '; //global options
		$ffmpegCmd.= '%6$s'; //output
		
		//If one file is missing we re-generate everything
		if (
			!file_exists($coverUrl) ||
			!file_exists($mp3Url)   ||
			!file_exists($videoUrl) ||
			!file_exists($jsonUrl) 
		) {

		//APIs Classes
			require_once(dirname(__FILE__) . "/classes/class.Api.php");
			require_once(dirname(__FILE__) . "/classes/class.Api.Deezer.php");
	
			$dzr = new apiDeezer();
			
		//get track data
			$track = $dzr->api("track/{$_GET['id']}");
			
			if (!isset($track->error)) {
				
				//GET cover
				$coverSize = 640;
				$cover = curl("{$track->album->cover}?size={$coverSize}");
				file_put_contents($coverUrl, $cover);	
				
				//GET mp3
				$mp3 = curl($track->preview);
				file_put_contents($mp3Url, $mp3);
				
				//CREATE VIDEO
				$cmd = sprintf($ffmpegCmd,$coverUrl,$mp3Url,$track->title,$track->artist->name,$track->album->title,$videoUrl);
				exec($cmd);
				
				//If we have all files we create and save the JSON
				if (
					file_exists($coverUrl) &&
					file_exists($mp3Url)   &&
					file_exists($videoUrl)
				) {
					
					$json = array(
						"id"    => $filename,
						"track" => array(
							"id" => $track->id,
							"link" => $track->link,
							"title" => $track->title,
							"artist" => $track->artist->name,
							"album" => $track->album->title
						),
						"cover" => $websiteUrl.$coverUrl,
						"mp3"   => $websiteUrl.$mp3Url,
						"video" => $websiteUrl.$videoUrl
					);
					
					file_put_contents($jsonUrl, json_encode($json));
					
				}
				else {
					
					$json = array(
						"error" => "file",
						"message" => "Creation file error",
						"files" => array(
							"id" => $filename,
							"cover" => file_exists($coverUrl),
							"mp3" => file_exists($mp3Url),
							"video" => file_exists($videoUrl)
						)
					);
					
				}				
				
			}
			else {
				$json = array(
					"error" => "api",
					"message" => "API error",
					"api_response" => $track
				);
			}//API RESPONSE
			
		}
		else {
			
			$json = json_decode(curl($websiteUrl.$jsonUrl), true);
			
		}//JSON EXIST

	}
	else {
		$json = array(
			"error" => "track_id",
			"message" => "No track ID"
		);
	}//TRACK ID
	
	header("content-type: application/json; charset=utf-8");
	echo(json_encode($json));
	
?>