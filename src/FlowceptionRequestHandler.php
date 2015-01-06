<?php
namespace Codeception\Extension;

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
	 * @var \TYPO3\Flow\Cli\Request
	 */
	protected $cliRequest;

	/**
	 * @var \TYPO3\Flow\Cli\Response
	 */
	protected $cliResponse;

	/**
	 * @var array
	 */
	protected $command;

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

		$this->setCliRequest($this->getRequestBuilder()->build($this->command));
		$this->setCliResponse(new \TYPO3\Flow\Cli\Response());

		$this->getDispatcher()->dispatch($this->cliRequest, $this->cliResponse);
		$this->cliResponse->send();

		$this->deferAutoloading();
	}

	public function setCommand(array $command) {
		$this->command = $command;
	}

	/**
	 * Returns the currently processed request
	 *
	 * @return \TYPO3\Flow\Cli\Request
	 */
	public function getCliRequest() {
		return $this->cliRequest;
	}

	/**
	 * Returns the response corresponding to the currently handled request
	 *
	 * @return \TYPO3\Flow\Cli\Response
	 */
	public function getCliResponse() {
		return $this->cliResponse;
	}

	/**
	 * Allows to set the currently processed request.
	 *
	 * @param \TYPO3\Flow\Cli\Request $request
	 * @see TYPO3\Flow\Cli\RequestBuilder::build()
	 */
	public function setCliRequest(\TYPO3\Flow\Cli\Request $request) {
		$this->cliRequest = $request;
	}

	/**
	 * Allows to set the currently processed response.
	 *
	 * @param \TYPO3\Flow\Cli\Response $response
	 */
	public function setCliResponse(\TYPO3\Flow\Cli\Response $response) {
		$this->cliResponse = $response;
	}

	/**
	 * Moves TYPO3 Flow's autoloader to the end of the SPL stack.
	 *
	 * @throws \TYPO3\Flow\Exception
	 */
	protected function deferAutoloading() {
		$classLoader = $this->bootstrap->getEarlyInstance('TYPO3\Flow\Core\ClassLoader');
		spl_autoload_unregister(array($classLoader, 'loadClass'));
		spl_autoload_register(array($classLoader, 'loadClass'), TRUE, FALSE);
	}

	/**
	 * @return \TYPO3\Flow\Cli\RequestBuilder
	 * @throws \TYPO3\Flow\Exception
	 */
	protected function getRequestBuilder() {
		return $this->bootstrap->getObjectManager()->get('TYPO3\\Flow\\Cli\\RequestBuilder');
	}

	/**
	 * @return \TYPO3\Flow\Mvc\Dispatcher
	 * @throws \TYPO3\Flow\Exception
	 */
	protected function getDispatcher() {
		return $this->bootstrap->getObjectManager()->get('TYPO3\\Flow\\Mvc\\Dispatcher');
	}

}
