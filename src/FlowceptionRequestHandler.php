<?php
namespace Codeception\Module;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Core\Bootstrap;

/**
 * @Flow\Proxy(false)
 * @Flow\Scope("singleton")
 */
class FlowceptionRequestHandler extends \TYPO3\Flow\Cli\CommandRequestHandler {

	/**
	 * @var \TYPO3\Flow\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * Constructor
	 *
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap
	 */
	public function __construct(Bootstrap $bootstrap) {
		$this->bootstrap = $bootstrap;
	}

	/**
	 * This request handler can handle CLI requests.
	 *
	 * @return bool If the request is a CLI request, TRUE otherwise FALSE
	 */
	public function canHandleRequest() {
		return parent::canHandleRequest();
	}

	/**
	 * Returns the priority - how eager the handler is to actually handle the
	 * request.
	 *
	 * As this request handler can only be used as a preselected request handler,
	 * the priority for all other cases is 0.
	 *
	 * @return int The priority of the request handler.
	 */
	public function getPriority() {
		return parent::getPriority();
	}

	/**
	 * Handles a command line request
	 */
	public function handleRequest() {
		$sequence = $this->bootstrap->buildRuntimeSequence();
		$sequence->invoke($this->bootstrap);
	}

}
