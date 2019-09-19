<?php
namespace wcf\data\unfurl;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\UnfurlURLJob;

/**
 * Contains all dbo actions for unfurl url objects. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * 
 * @method	UnfurlUrlEditor[]	getObjects()
 * @method	UnfurlUrlEditor	        getSingleObject()
 */
class UnfurlUrlAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	public function create() {
		/** @var UnfurlUrl $object */
		$object = parent::create();
		
		BackgroundQueueHandler::getInstance()->enqueueIn([
			new UnfurlURLJob($object)
		]);
		
		BackgroundQueueHandler::getInstance()->forceCheck();
		
		return $object;
	}
	
	/**
	 * Returns the unfurl url object to a given url. 
	 * 
	 * @return UnfurlUrl
	 */
	public function findOrCreate() {
		$object = UnfurlUrl::getByUrl($this->parameters['data']['url']); 
		
		if (!$object->urlID) {
			$returnValues = (new self([], 'create', [
				'data' => [
					'url' => $this->parameters['data']['url']
				]
			]))->executeAction();
			
			return $returnValues['returnValues'];
		}
		
		return $object;
	}
}
