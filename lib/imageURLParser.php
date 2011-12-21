<?php

class ImageURLParser {

	static function isImageFileURL ($url) {
		if (preg_match("/\.(?:gif|jpe?g|png|svg)$/", $url)) {
			return true;
		}

		return false;
	}


	static function isImageURL ($url) {
		if (ImageURLParser::isImageFileURL($url) || ImageURLParse::getServiceImageURL($url) !== null) {
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

		if (ImageURLParser::hasSuffix($host, 'twitpic.com')) {
			$path = substr($path, 1);

			if (ImageURLParser::hasSuffix($path, '/full')) {
				$path = substr($path, 0, -5);
			}

			if (ImageURLParser::isAlphaNumericOnly($path)) {
				return sprintf('http://twitpic.com/show/mini/%s', $path);
			}
		} else if (ImageURLParser::hasSuffix($host, 'plixi.com')) {
			$path = substr($path, 1);
			return sprintf('http://api.plixi.com/api/TPAPI.svc/imagefromurl?size=thumbnail&url=%s', $url);
		} else if (ImageURLParser::hasSuffix($host, 'yflow.com')) {
			$path = substr($path, 1);
			return sprintf("%s:small", $url);
		}
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

}

