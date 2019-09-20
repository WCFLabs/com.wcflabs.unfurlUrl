<?php 
namespace wcf\util;
use wcf\data\package\PackageCache;
use wcf\system\exception\HTTPNotFoundException;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\WCF;
use wcf\util\exception\HTTPException;

/**
 * Helper class to unfurl specific urls.
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UnfurlUrlUtil {
	/**
	 * The url, which contains the informations. 
	 * @var string
	 */
	private $url;
	
	/**
	 * The body of the website.
	 * @var string
	 */
	private $body;
	
	/**
	 * The dom document of the fetched website. 
	 * @var \DOMDocument
	 */
	private $domDocument;
	
	/**
	 * UnfurlUrlUtil constructor.
	 *
	 * @param string $url
	 */
	public function __construct($url) {
		if (!Url::is($url)) {
			throw new \InvalidArgumentException('Given URL "'. $url . '" is not a valid URL.');
		}
		
		$this->url = $url;
		
		$this->fetchUrl();
	}
	
	/**
	 * Fetches the body of the given url and converts the body to utf-8. 
	 * 
	 * @throws \wcf\system\exception\SystemException
	 * @throws exception\HTTPException
	 */
	private function fetchUrl() {
		try {
			$request = new HTTPRequest($this->url, [
				'maxLength' => 10 * (1 << 20),
				'maxDepth' => 3
			]);
			// set own user agent, which contains the package identifier to block the bot to unfurly urls 
			// @TODO find better ua
			$request->addHeader('user-agent', "HTTP.PHP (HTTPRequest.class.php; WoltLab Suite/".WCF_VERSION."/com.wcflabs.unfurlUrl; ".(WCF::getLanguage()->languageCode ? WCF::getLanguage()->languageCode : 'en').")");
			$request->execute();
			
			$this->body = $request->getReply()['body'];
			
			if (mb_detect_encoding($this->body) !== 'UTF-8') {
				$this->body = StringUtil::convertEncoding(mb_detect_encoding($this->body), 'UTF-8', $this->body);
			}
		}
		catch (HTTPNotFoundException $e) {}
		catch (HTTPUnauthorizedException $e) {}
	}
	
	/**
	 * Returns the dom document of the website. 
	 * 
	 * @return \DOMDocument
	 * @throws \Exception
	 */
	private function getDomDocument() {
		if ($this->domDocument === null) {
			try {
				libxml_use_internal_errors(true);
				$this->domDocument = new \DOMDocument();
				$this->domDocument->loadHTML('<?xml version="1.0" encoding="UTF-8"?>'.$this->body);
			}
			catch (\Exception $e) {
				// the given body is no valid XML
				$this->domDocument = false; 
				
				throw $e;
			}
		}
		
		return $this->domDocument;
	}
	
	/**
	 * Determines the title of the website. 
	 * 
	 * @return string|null
	 * @throws \Exception
	 */
	public function getTitle() {
		if (!empty($this->body)) {
			$metaTags = $this->getDomDocument()->getElementsByTagName('meta');
			
			// twitter:description
			reset($metaTags);
			foreach ($metaTags as $metaTag) {
				foreach ($metaTag->attributes as $attr) {
					if ($attr->nodeName == 'property' && $attr->value == 'twitter:title') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'content') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// og 
			foreach ($metaTags as $metaTag) {
				foreach ($metaTag->attributes as $attr) {
					if ($attr->nodeName == 'property' && $attr->value == 'og:title') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'content') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// swiftype https://swiftype.com/documentation/site-search/crawler-configuration/meta-tags 
			// @TODO 
			
			// title tag
			$title = $this->getDomDocument()->getElementsByTagName('title');
			if ($title->length) {
				return $title->item(0)->nodeValue;
			}
		}
		
		return null;
	}
	
	/**
	 * Determines the description of the website. 
	 * 
	 * @return string|null
	 * @throws \Exception
	 */
	public function getDescription() {
		if (!empty($this->body)) {
			$metaTags = $this->getDomDocument()->getElementsByTagName('meta');
			
			// twitter:description
			foreach ($metaTags as $metaTag) {
				foreach ($metaTag->attributes as $attr) {
					if ($attr->nodeName == 'property' && $attr->value == 'twitter:description') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'content') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// og:description
			reset($metaTags);
			foreach ($metaTags as $metaTag) {
				foreach ($metaTag->attributes as $attr) {
					if ($attr->nodeName == 'property' && $attr->value == 'og:description') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'content') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// meta description
			reset($metaTags);
			foreach ($metaTags as $metaTag) {
				foreach ($metaTag->attributes as $attr) {
					if ($attr->nodeName == 'name' && $attr->value == 'description') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'content') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// wikipedia 
			$element = $this->getDomDocument()->getElementById('mw-content-text');
			if ($element !== null) {
				// first remove tables 
				$tables = $element->getElementsByTagName('table');
				
				while ($tables->length) {
					DOMUtil::removeNode($tables->item(0));
				}
				
				$p = $element->getElementsByTagName('p');
				
				if ($p->length) {
					/** @var \DOMElement $first */
					$first = $p->item(0);
					
					// remove subs 
					$subs = $first->getElementsByTagName('sup');
					
					while ($subs->length) {
						DOMUtil::removeNode($subs->item(0));
					}
					
					$styles = $first->getElementsByTagName('style');
					
					while ($styles->length) {
						DOMUtil::removeNode($styles->item(0));
					}
					
					// remove no printable 
					$spans = $first->getElementsByTagName('span');
					for ($i = $spans->length - 1; $i >= 0; $i--) {
						if ($spans->item($i)->attributes->getNamedItem('class') !== null && $spans->item($i)->attributes->getNamedItem('class')->nodeValue == 'noprint') {
							DOMUtil::removeNode($spans->item($i));
						}
					}
					
					return StringUtil::stripHTML($first->nodeValue);
				}
			}
		}
		
		return null;
	}
	
	/**
	 * Returns the image url for the current url.
	 *  
	 * @return string|null
	 * @throws \Exception
	 */
	public function getImageUrl() {
		if (!empty($this->body)) {
			$metaTags = $this->getDomDocument()->getElementsByTagName('meta');
			
			// twitter:image:src
			foreach ($metaTags as $metaTag) {
				foreach ($metaTag->attributes as $attr) {
					if ($attr->nodeName == 'property' && $attr->value == 'twitter:image:src') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'content') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// og:image
			reset($metaTags);
			foreach ($metaTags as $metaTag) {
				foreach ($metaTag->attributes as $attr) {
					if ($attr->nodeName == 'property' && $attr->value == 'og:image') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'content') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// apple touch icon 
			$linkTags = $this->getDomDocument()->getElementsByTagName('link');
			
			foreach ($linkTags as $linkTag) {
				foreach ($linkTag->attributes as $attr) {
					if ($attr->nodeName == 'rel' && $attr->value == 'apple-touch-icon') {
						foreach ($attr->parentNode->attributes as $attr) {
							if ($attr->nodeName == 'href') {
								return $attr->value;
							}
						}
					}
				}
			}
			
			// @TODO manifest
		}
		
		return null; 
	}
	
	/**
	 * Downloads the image from a url and returns the image body. 
	 * 
	 * @param $url
	 * @return string|null
	 * @throws \Exception
	 */
	public static function downloadImageFromUrl($url) {
		try {
			// download image
			try {
				// rewrite schemaless URLs to https
				$scheme = parse_url($url, PHP_URL_SCHEME);
				if (!$scheme) {
					if (StringUtil::startsWith($url, '//')) {
						$url = 'https:'.$url;
					}
					else {
						throw new \DomainException();
					}
				}
				
				$request = new HTTPRequest($url, [
					'maxLength' => 10 * (1 << 20) // download at most 10 MiB
				]);
				$request->addHeader('Accept', 'image/*');
				$request->execute();
			}
			catch (\Exception $e) {
				$chain = $e;
				do {
					if ($chain instanceof HTTPException) {
						throw new \DomainException();
					}
					
					if (strpos($chain->getMessage(), 'Can not connect to') === 0) {
						throw new \DomainException();
					}
				}
				while ($chain = $chain->getPrevious());
				
				throw $e;
			}
			
			$image = $request->getReply()['body'];
			
			// check file type
			$imageData = @getimagesizefromstring($image);
			if (!$imageData) throw new \DomainException();
			
			switch ($imageData[2]) {
				case IMAGETYPE_PNG:
				case IMAGETYPE_GIF:
				case IMAGETYPE_JPEG:
					break;
				default:
					throw new \DomainException();
			}
			
			return $image;
		}
		catch (\DomainException $e) {
			return null;
		}
	}
	
	/**
	 * Returns the package id for the unfurl url package. 
	 * 
	 * @return int
	 */
	public static final function getPackageID() {
		return PackageCache::getInstance()->getPackageByIdentifier('com.wcflabs.unfurlUrl')->packageID;
	}
	
	/**
	 * Returns the unfurl url attr unique for each installation. 
	 * 
	 * @return string
	 */
	public static final function getAttrName() {
		return 'unfurl-url-'. self::getPackageID() .'-id';
	}
}
