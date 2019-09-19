<?php
namespace wcf\data\unfurl;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of unfurled urls.
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method	UnfurlUrl		current()
 * @method	UnfurlUrl[]		getObjects()
 * @method	UnfurlUrl|null	        search($objectID)
 * @property	UnfurlUrl[]	        $objects
 */
class UnfurlUrlList extends DatabaseObjectList {}
