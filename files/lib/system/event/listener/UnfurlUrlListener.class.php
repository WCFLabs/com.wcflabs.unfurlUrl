<?php
namespace wcf\system\event\listener;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\html\node\HtmlNodeUnfurlLink;

/**
 * A listener, to identify and mark links, which can be unfurled. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UnfurlUrlListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		/** @var HtmlInputNodeProcessor $eventObj */
		foreach ($eventObj->plainLinks as $link) {
			HtmlNodeUnfurlLink::setUnfurl($link);
		}
	}
}
