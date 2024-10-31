<?php
/*
Plugin Name: oEmbedder
Plugin URI: http://janwillemeshuis.nl/wordpress-plugins/oembedder-plugin/
Description: Make's it easy to use oEmbed uri's from Flickr, YouTube, Polldady and others.
Version: 0.0.2
Author: Jan Willem Eshuis
Author URI: http://janwillemeshuis.nl/
*/
define('oEmbedderTag','[oembed:');
define('oEmbedderTypePhoto','photo');
define('oEmbedderTypeVideo','video');
define('oEmbedderTypeRich','rich');

function resultType($content) {
	if ($content[0]=='{') return 'json';
	return 'xml';
}

function contentType($contentInfo) {
	if ($contentInfo->type==oEmbedderTypeRich) return oEmbedderTypeRich;
	if ($contentInfo->type==oEmbedderTypePhoto) return oEmbedderTypePhoto;
	if ($contentInfo->type==oEmbedderTypeVideo) return oEmbedderTypeVideo; 
	return 'none';
}

function parsePhotoItem($embedInfo) {
	$html = '<img src="'.$embedInfo->url.'" ';
	$html .= 'width="'.$embedInfo->width.'" ';
	$html .= 'height="'.$embedInfo->height.'" ';
	$html .= 'alt="Author: '.$embedInfo->author_name.'" ';
	$html .= '/>';
	return $html;
}

function parseRichItem($embedInfo) {
	return $embedInfo->html;
}

function parseVideoItem($embedInfo) {
	return $embedInfo->html;
}

function getExternalElement($uri) {
	$content = implode(file($uri));
	if (resultType($content)=='json') {
		$embedInfo = json_decode($content);
	} else {
		$embedInfo = simplexml_load_string($content);
	}
	if (isset($embedInfo)) {
		switch (contentType($embedInfo)) {
			case oEmbedderTypeVideo : return parseVideoItem($embedInfo);
			case oEmbedderTypePhoto : return parsePhotoItem($embedInfo);
			case oEmbedderTypeRich : return parseRichItem($embedInfo);
		}
	}
	return '';
}

function parseEmbedElement($body,$match) {
	$uri = substr($match,strlen(oEmbedderTag),-1);
	$html = getExternalElement($uri);
	if (strlen($html)>0) {
		$body = str_replace($match,$html,$body);
	}
	return $body;
}

function replaceOembedTags($body) {
	$pattern = '/\[oembed:.+\]/';
	$matches = array();
	if (preg_match_all($pattern,$body,$matches)) {
		if (isset($matches[0])) {
			foreach ($matches[0] as $match) {
				$body = parseEmbedElement($body,$match);
			}
		}
	}
	return $body;
}

add_filter('the_content', 'replaceOembedTags');

?>