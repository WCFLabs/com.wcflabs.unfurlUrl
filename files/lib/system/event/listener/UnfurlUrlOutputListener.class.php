<?php
namespace wcf\system\event\listener;
use wcf\system\html\output\node\HtmlOutputNodeProcessor;
use wcf\system\html\output\node\HtmlUnfurlUrlOutputNodeProcessor;

/**
 * A listener to unfurl urls in the output.
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UnfurlUrlOutputListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		/** @var HtmlOutputNodeProcessor $eventObj */
		HtmlUnfurlUrlOutputNodeProcessor::unfurl($eventObj);
	}
}
