<?php
namespace wcf\system\html\output\node;

/**
 * Helper class to bypass an new node into the html output node processor.
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HtmlUnfurlUrlOutputNodeProcessor extends HtmlOutputNodeProcessor {
	/**
	 * Invokes the unfurl url node for a html output node processor. 
	 * 
	 * @param       HtmlOutputNodeProcessor         $processor
	 */
	public static function unfurl(HtmlOutputNodeProcessor $processor) {
		$processor->invokeHtmlNode(new HtmlOutputUnfurlUrlNode());
	}
}