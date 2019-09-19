<?php
namespace wcf\data\unfurl;
use wcf\data\DatabaseObjectEditor;

/**
 * Provide functions to edit an unfurl url.
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method	UnfurlUrl	getDecoratedObject()
 * @mixin	UnfurlUrl
 */
class UnfurlUrlEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = UnfurlUrl::class;
}
