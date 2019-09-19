<?php
namespace wcf\system\html\output\node;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\util\StringUtil;
use wcf\util\UnfurlUrlUtil;

/**
 * Node class to replace unfurled urls in the output. 
 * 
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HtmlOutputUnfurlUrlNode extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'a';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			if ($this->outputType === 'text/html' && !empty($element->getAttribute(UnfurlUrlUtil::getAttrName())) && MessageEmbeddedObjectManager::getInstance()->getObject('com.wcflabs.unfurlUrl.url', $element->getAttribute(UnfurlUrlUtil::getAttrName())) !== null) {
				$nodeIdentifier = StringUtil::getRandomID();
				$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, ['urlId' => $element->getAttribute(UnfurlUrlUtil::getAttrName())]);
				
				$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		return MessageEmbeddedObjectManager::getInstance()->getObject('com.wcflabs.unfurlUrl.url', $data['urlId'])->render();
	}
}
