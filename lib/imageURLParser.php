<?php

class ImageURLParser {

	static function isImageFileURL ($url) {
		if (preg_match("/\.(?:gif|jpe?g|png|svg)$/", $url)) {
			return true;
		}

		return false;
	}


	static function isImageURL ($url) {
		if (ImageURLParser::isImageFileURL($url) || ImageURLParser::getServiceImageURL($url) !== null) {
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

		# twitpic.com
		if (ImageURLParser::hasSuffix($host, 'twitpic.com')) {
			$path = substr($path, 1);

			if (ImageURLParser::hasSuffix($path, '/full')) {
				$path = substr($path, 0, -5);
			}

			if (ImageURLParser::isAlphaNumericOnly($path)) {
				return sprintf('http://twitpic.com/show/mini/%s', $path);
			}
		}
		# plixi.com
		else if (ImageURLParser::hasSuffix($host, 'plixi.com')) {
			$path = substr($path, 1);
			return sprintf('http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=thumbnail&url=%s', $url);
		}
		# yflow.com
		else if (ImageURLParser::hasSuffix($host, 'yflow.com')) {
			$path = substr($path, 1);
			return sprintf("%s:small", $url);
		}
		# twitgoo.com
		else if (ImageURLParser::hasSuffix($host, 'twitgoo.com')) {
			$path = substr($path, 1);

			if (ImageURLParser::isAlphaNumericOnly($path)) {
				return sprintf('http://twitgoo.com/show/mini/%s', $path);
			}
		}
		# img.ly
		else if ($host === 'img.ly') {
			$path = substr($path, 1);

			if (ImageURLParser::isAlphaNumericOnly($path)) {
				return sprintf('http://img.ly/show/mini/%s', $path);
			}
		}
		# imgur.com 
		else if ($host === 'imgur.com') {
			if (ImageURLParser::hasSuffix($path, '/gallery/')) {
				$path = substr($path, 9);

				if (ImageURLParser::isAlphaNumericOnly($path)) {
					return sprintf('http://i.imgur.com/%s.jpg', $path);
				}
			}

			$path = substr($path, 1);

			if (ImageURLParser::isAlphaNumericOnly($path)) {
				return sprintf('http://i.imgur.com/%s.jpg', $path);
			}
		}
		# flickr
		else if (ImageURLParser::hasSuffix($host, 'flic.kr')) {
			$short_id = substr($path, 3);
			return sprintf('http://flic.kr/p/img/%@_m.jpg', $short_id);
		}
		# instagram
		else if (ImageURLParser::hasSuffix($host, 'instagr.am')) {
			$short_id = substr($path, 3);
			return sprintf('http://instagrnam/p/%s/media/?size=m', $short_id);
		}
		# movapic
		else if (ImageURLParser::hasSuffix($host, 'movapic.com')) {
			if (ImageURLParser::hasSuffix($path, '/pic/')) {
				$path = substr($path, 5);
				if (ImageURLParser::isAlphaNumericOnly($path)) {
					return sprintf('http://image.movapic.com/pic/t_%s.jpeg', $path);
				}
			}
		}
		# hatena
		else if (ImageURLParser::hasSuffix($path, 'f.hatena.ne.jp')) {
			$paths = explode('/', $path);

			if (count($paths) > 3) {
				$user_id = $paths[1];
				$photo_id = $paths[2];

				if (!empty($user_id) && is_float($photo_id)) {
					return sprintf('http://img.f.hatena.ne.jp/images/fotolife/%@/%@/%@/%@.jpg', substr($user_id, 0, 1), $user_id, substr($photo_id, 0, 8), $photo_id);
				}
			}
		}
		# pikubo.jp
		else if (ImageURLParser::hasSuffix($host, 'pikubo.jp')) {
			if (ImageURLParser::hasPrefix($path, '/photo/') && strlen($path) >= 29) {
				$path = substr($path, 7, 22);
				return sprintf('http://pikubo.jp/p/p/%s', $path);
			}
		}
		# pikubo.me
		else if (ImageURLParser::hasSuffix($host, 'pikubo.me')) {
			$path = substr($path, 1);
			return sprintf('http://pikubo.me/p/%s', $path);
		}
/*
		#
		else if () {

		}
		#
		else if () {

		}
		#
		else if () {

		}
		#
		else if () {

		}
		#
		else if () {

		}
		#
		else if () {

		}
		#
		else if () {

		}
		#
		else if () {

		}
		#
		else if () {

		}
*/

		return null;
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

