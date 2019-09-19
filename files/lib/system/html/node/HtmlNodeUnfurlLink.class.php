<?php
namespace wcf\system\html\node;
use wcf\data\unfurl\UnfurlUrlAction;
use wcf\util\UnfurlUrlUtil;

/**
 * Helper class to manipulate link objects. 
 * 
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class HtmlNodeUnfurlLink extends HtmlNodePlainLink {
	/**
	 * Marks a link element with the UnfurlUrlID.
	 * 
	 * @param       HtmlNodePlainLink       $link
	 */
	public static function setUnfurl(HtmlNodePlainLink $link) {
		if ($link->isStandalone()) {
			$object = new UnfurlUrlAction([], 'findOrCreate', [
				'data' => [
					'url' => $link->href
				]
			]);
			$returnValues = $object->executeAction();
			$link->topLevelParent->firstChild->setAttribute(UnfurlUrlUtil::getAttrName(), $returnValues['returnValues']->urlID);
		}
	}
}
