<?php
namespace wcf\data\unfurl;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\Url;

/**
 * Represents an unfurl url object in the database. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * 
 * @property-read string $url
 * @property-read string $urlHash
 * @property-read string $title
 * @property-read string $description
 */
class UnfurlUrl extends DatabaseObject {
	/**
	 * Renders the unfurl url card and returns the template. 
	 * 
	 * @return string
	 */
	public function render() {
		return WCF::getTPL()->fetch('unfurlUrl', 'wcf', [
			'object' => $this
		]);
	}
	
	/**
	 * Returns the hostname of the url. 
	 * 
	 * @return string
	 */
	public function getHost() {
		$url = Url::parse($this->url);
		
		return $url->offsetGet(PHP_URL_HOST);
	}
	
	/**
	 * Returns the unfurl url object for a given url. 
	 * 
	 * @param string $url
	 * @return UnfurlUrl
	 */
	public static function getByUrl($url) {
		if (!\wcf\util\Url::is($url)) {
			throw new \InvalidArgumentException("Given URL is not valid.");
		}
		
		$sql = "SELECT		unfurl_url.*
			FROM		wcf".WCF_N."_unfurl_url unfurl_url
			WHERE		unfurl_url.urlHash = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([sha1($url)]);
		$row = $statement->fetchArray();
		if (!$row) $row = [];
		
		return new self(null, $row);
	}
}
