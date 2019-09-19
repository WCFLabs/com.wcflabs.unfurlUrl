<?php
namespace wcf\system\message\embedded\object;
use wcf\data\unfurl\UnfurlUrlList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\util\UnfurlUrlUtil;

/**
 * Represents the unfurl url embedded object handlers. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UnfurlUrlEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		$urlList = new UnfurlUrlList();
		$urlList->getConditionBuilder()->add('unfurl_url.urlID IN (?)', [$objectIDs]);
		$urlList->readObjects();
		return $urlList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		$unfurlUrlIDs = [];
		foreach ($htmlInputProcessor->getHtmlInputNodeProcessor()->getDocument()->getElementsByTagName('a') as $element) {
			/** @var \DOMElement $element */
			$id = $element->getAttribute(UnfurlUrlUtil::getAttrName());
			
			if (!empty((int)$id)) {
				$unfurlUrlIDs[] = (int) $id;
			}
		}
		
		return $unfurlUrlIDs;
	}
}
