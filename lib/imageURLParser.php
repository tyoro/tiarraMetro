<?php

// via https://github.com/psychs/limechat/blob/master/Classes/Views/Log/ImageURLParser.m
// LICENSE GPL v2

// via http://xxmacoxx.posterous.com/twitpic-url-blogironsjp

class ImageURLParser {

	static function isImageFileURL ($url) {
		if (preg_match("/\.(?:gif|jpe?g|png|svg)$/i", $url)) {
			return true;
		}

		return false;
	}


	static function isImageURL ($url) {
		if (self::isImageFileURL($url) || self::getServiceImageURL($url) !== null) {
			return true;
		}

		return false;
	}

	static function getServiceImageURL ($url) {
		$url = trim($url);

		$parsed_url = parse_url($url);
		$host = !empty($parsed_url['host']) ? strtolower($parsed_url['host']) : '' ;		
		$path = !empty($parsed_url['path']) ? $parsed_url['path'] : '';

		if (empty($host) || empty($path)) return null;

		$image_url = null;
		$thumb_url = null;
		$player = 'img';

		# twitpic.com
		if (self::hasSuffix($host, 'twitpic.com')) {
			$path = substr($path, 1);

			if (self::hasPrefix($path, '/full')) {
				$path = substr($path, 0, -5);
			}

			if (self::isAlphaNumericOnly($path)) {
				$image_url = sprintf('http://twitpic.com/show/full/%s', $path);
				$thumb_url = sprintf('http://twitpic.com/show/thumb/%s', $path);
			}
		}
		# plixi.com
		else if (self::hasSuffix($host, 'plixi.com')) {
			$path = substr($path, 1);
			$image_url = sprintf('http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=big&url=%s', $url);
			$thumb_url = sprintf('http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=thumbnail&url=%s', $url);
		}
		# lockerz.com
		else if (self::hasSuffix($host, 'lockerz.com')) {
			$path = substr($path, 1);
			$image_url = sprintf('http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=medium&url=%s', $url);
			$thumb_url = sprintf('http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=thumbnail&url=%s', $url);
			//thumbnail”, “small”, “mobile”, “medium” or “big”
		}
		# yfrog.com
		else if (self::hasSuffix($host, 'yfrog.com')) {
			$path = substr($path, 1);
			$image_url = sprintf("%s:small", $url);
			$thumb_url = $image_url;
		}
		# twitgoo.com
		else if (self::hasSuffix($host, 'twitgoo.com')) {
			$path = substr($path, 1);

			if (self::isAlphaNumericOnly($path)) {
				$image_url = sprintf('http://twitgoo.com/show/image/%s', $path);
				$thumb_url = sprintf('http://twitgoo.com/show/thumb/%s', $path);
				//$thumb_url = sprintf('http://twitgoo.com/show/mini/%s', $path);
			}
		}
		# img.ly
		else if ($host === 'img.ly') {
			$path = substr($path, 1);

			if (self::isAlphaNumericOnly($path)) {
				$image_url = sprintf('http://img.ly/show/thumb/%s', $path);
				$thumb_url = $image_url;
				//$image_url = sprintf('http://img.ly/show/mini/%s', $path);
			}
		}
		# imgur.com 
		else if ($host === 'imgur.com') {
			if (self::hasPrefix($path, '/gallery/')) {
				$path = substr($path, 9);

				if (self::isAlphaNumericOnly($path)) {
					$image_url = sprintf('http://i.imgur.com/%sl.jpg', $path);
					$thumb_url = sprintf('http://i.imgur.com/%ss.jpg', $path);
				}
			}else{
				$path = substr($path, 1);

				if (self::isAlphaNumericOnly($path)) {
					$image_url = sprintf('http://i.imgur.com/%sl.jpg', $path);
					$thumb_url = sprintf('http://i.imgur.com/%ss.jpg', $path);
				}
			}
		}
		# flickr
		else if (self::hasSuffix($host, 'flic.kr')) {
			$short_id = substr($path, 3);
			$short_id = preg_replace('/\/?$/', '', $short_id);
			$image_url = sprintf('http://flic.kr/p/img/%s_m.jpg', $short_id);
			$thumb_url = sprintf('http://flic.kr/p/img/%s_t.jpg', $short_id);
		}
		# instagram
		else if (self::hasSuffix($host, 'instagr.am')) {
			$short_id = substr($path, 3);
			$short_id = preg_replace('/\/?$/', '', $short_id);
			$image_url = sprintf('http://instagr.am/p/%s/media/?size=m', $short_id);
			//$image_url = sprintf('http://instagr.am/p/%s/media/?size=l', $short_id);
			$thumb_url = sprintf('http://instagr.am/p/%s/media/?size=t', $short_id);
		}
		# movapic
		else if (self::hasSuffix($host, 'movapic.com')) {
			if (self::hasPrefix($path, '/pic/')) {
				$path = substr($path, 5);
				if (self::isAlphaNumericOnly($path)) {
					$image_url = sprintf('http://image.movapic.com/pic/m_%s.jpeg', $path);
					$thumb_url = sprintf('http://image.movapic.com/pic/t_%s.jpeg', $path);
					//$thumb_url = sprintf('http://image.movapic.com/pic/m_%s.jpeg', $path);
				}
			}
		}
		# hatena
		else if (self::hasSuffix($path, 'f.hatena.ne.jp')) {
			$paths = explode('/', $path);

			if (count($paths) > 3) {
				$user_id = $paths[1];
				$photo_id = $paths[2];

				if (!empty($user_id) && is_float($photo_id)) {
					 $image_url = sprintf(
						'http://img.f.hatena.ne.jp/images/fotolife/%s/%s/%s/%s.jpg',
						substr($user_id, 0, 1),
						$user_id,
						substr($photo_id, 0, 8),
						$photo_id
					);
					$thumb_url = $image_url;
				}
			}
		}
		# pikubo.jp
		else if (self::hasSuffix($host, 'pikubo.jp')) {
			if (self::hasPrefix($path, '/photo/') && strlen($path) >= 29) {
				$path = substr($path, 7, 22);
				$image_url = sprintf('http://pikubo.jp/p/p/%s', $path);
				$thumb_url = $image_url;
			}
		}
		# pikubo.me
		else if (self::hasSuffix($host, 'pikubo.me')) {
			$path = substr($path, 1);
			$image_url = sprintf('http://pikubo.me/p/%s', $path);
			$thumb_url = $image_url;
		}
		# puu.sh
		else if (self::hasSuffix($host, 'puu.sh')) {
			if( strlen( $path ) ){
				$image_url = $url;
				$thumb_url = $image_url;
			}
		}
		# youtube.com
		else if (self::hasSuffix($host, 'youtube.com')) {
			$player = 'swf';
			$parameters = self::getParametersFromQuery($parsed_url['query']);

			if (!empty($parameters['v'])) {
				$image_url = sprintf('http://youtube.com/v/%s', $parameters['v']);
				$thumb_url = sprintf('http://img.youtube.com/vi/%s/default.jpg', $parameters['v']);
			}
		} else if ($host === 'youtu.be') {
			$player = 'swf';
			$path = substr($path, 1);
			$image_url = sprintf('http://youtube.com/v/%s', $path);
			$thumb_url = sprintf('http://img.youtube.com/vi/%s/default.jpg', $path);
		}
/*
		# twitvid.com
		else if () {

		}
*/
		# nicovideo.jp
		else if (self::hasSuffix($host, 'nicovideo.jp')) {
			$vid = null;
			$iid = null;
			if (self::hasPrefix($path, '/watch/') ) {
				$player = 'swf';
				$path = substr($path, 7);
				if( self::hasPrefix($path, 'sm' ) || self::hasPrefix($path, 'nm' ) ){
					$vid = $path;
				}
			}else if (self::hasPrefix($path, '/seiga/') ) {
				$path = substr($path, 7);
				if( self::hasPrefix($path, 'im' ) ){
					$iid = $path;
				}
			}

			if( $vid && strlen( $vid ) > 2 ){
				$vid = substr($vid, 2);
				$thumb_url = sprintf("http://tn-skr%s.smilevideo.jp/smile?i=%s", ($vid%4+1), $vid);
			}
			else if( $iid && strlen( $iid ) > 2 ){
				$iid = substr($iid, 2);
				$image_url = sprintf("http://lohas.nicoseiga.jp/thumb/%si",$iid);
				$thumb_url = $image_url;
			}

		}
		# gyazo.com
		else if ( $host === 'gyazo.com' ) {
			$path = substr($path, 1);
			$image_url = sprintf('http://cache.gyazo.com%s.png', $path);
			$thumb_url = $image_url;
		}
		# miil.me
		else if ($host === 'miil.me') {
                        if( strlen( $path ) ){
                                $image_url = $url;
                                $thumb_url = $image_url . '.jpeg'; # . '&size=256'
                        }
		}
		# picplz.com
		else if ($host === 'picplz.com') {
			if( strlen( $path ) ){
				$image_url = $url;
				$thumb_url = $image_url . '/thumb'; # . '/400'
			}
		}
                # photozou.jp
                else if ($host === 'photozou.jp') {
			$paths = explode('/', substr($path, 1));

			$image_url = $url;
			$thumb_url = 'http://photozou.jp/p/thumb/' . $paths[count($paths)-1]; #  'p/img/' . $paths[count($paths)-1]
                }

/*
		#
		else if () {

		}
		#
		else if () {

		}
*/

		if( empty($thumb_url) ){
			return null;
		}
		return array( $image_url, $thumb_url, $player );
	}

	static function hasPrefix ($value, $prefix) {
		$pattern = '/^' . preg_quote($prefix, '/') . '/';

		return (preg_match($pattern, $value) === 1);
	}

	static function hasSuffix ($value, $suffix) {
		$pattern = '/' . preg_quote($suffix, '/') . '$/';

		return (preg_match($pattern, $value) === 1);
	}

	static function isAlphaNumericOnly ($value) {
		return (preg_match('/^[0-9a-zA-Z]+$/', $value) === 1);
	}

	static function getParametersFromQuery ($query, $delimiter = '&', $should_decode = true) {
		$parameters = array();

		$queries = explode($delimiter, $query);

		foreach ($queries as $pair) {
			list($name, $value) = explode('=', $pair);

			if (!empty($should_decode)) {
				$name = urldecode($name);
				$value = urldecode($value);
			}

			$new = null;

			if (!empty($parameters[$name])) {
				if (is_array($parameters[$name])) {
					$new = $parameters[$name];
				} else {
					$new = array($parameters[$name]);
				}

				$new[] = $value;
			} else {
				$new = $value;
			}

			$parameters[$name] = $new;
		}

		return $parameters;
	}
}

