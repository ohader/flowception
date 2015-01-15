<?php
namespace Codeception\Module;

use Codeception\Exception\Extension as ExtensionException;

class Flowception extends \Codeception\Module {

	protected $config = array(
		'rootPath' => '.',
		'context' => 'Testing',
	);

	/**
	 * @var \TYPO3\Flow\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var FlowceptionRequestHandler
	 */
	protected $requestHandler;

	/**
	 * @var \TYPO3\Flow\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\Flow\Cli\RequestBuilder
	 */
	protected $requestBuilder;

	/**
	 * @var \TYPO3\Flow\Mvc\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Initializes this module.
	 *
	 * @throws ExtensionException
	 */
	public function _initialize() {
		if (!$this->bootstrap) {
			$this->_initializeFlow();
		}
	}

	/**
	 * Initializes TYPO3 Flow.
	 *
	 * @throws ExtensionException
	 * @throws \TYPO3\Flow\Exception
	 */
	protected function _initializeFlow() {
		$rootPath = realpath($this->config['rootPath']);

		if (!is_dir($rootPath)) {
			throw new ExtensionException($this, 'TYPO3 Flow root path is not valid "' . $rootPath . '"');
		}

		$_SERVER['FLOW_ROOTPATH'] = $rootPath;

		$this->bootstrap = new \TYPO3\Flow\Core\Bootstrap($this->config['context']);
		$this->requestHandler = new FlowceptionRequestHandler($this->bootstrap);
		$this->bootstrap->registerRequestHandler($this->requestHandler);
		$this->bootstrap->setPreselectedRequestHandlerClassName('Codeception\\Module\\FlowceptionRequestHandler');
		$this->bootstrap->run();

		$this->objectManager = $this->bootstrap->getObjectManager();
		$this->requestBuilder = $this->objectManager->get('TYPO3\\Flow\\Cli\\RequestBuilder');
		$this->dispatcher = $this->objectManager->get('TYPO3\\Flow\\Mvc\\Dispatcher');

		$this->deferAutoloading();
	}

	/**
	 * Modifies SPL autoloading concerning TYPO3 Flow.
	 *
	 * @param \Codeception\Step $step
	 */
	public function _beforeStep(\Codeception\Step $step) {
		$this->promoteAutoloading();
	}

	/**
	 * Modifies SPL autoloading concerning TYPO3 Flow.
	 *
	 * @param \Codeception\Step $step
	 */
	public function _afterStep(\Codeception\Step $step) {
		$this->deferAutoloading();
	}

	/**
	 * Executes a TYPO3 Flow command.
	 *
	 * @param string $command
	 * @return string
	 * @throws \TYPO3\Flow\Mvc\Exception\InfiniteLoopException
	 * @throws \TYPO3\Flow\Mvc\Exception\InvalidArgumentNameException
	 */
	public function executeFlowCommand($command) {
		$request = $this->requestBuilder->build($command);
		$response = new \TYPO3\Flow\Cli\Response();
		$this->dispatcher->dispatch($request, $response);
		return $response->getContent();
	}

	/**
	 * Executes a TYPO3 Flow command.
	 *
	 * @param string $command
	 * @return string
	 * @throws \TYPO3\Flow\Mvc\Exception\InfiniteLoopException
	 * @throws \TYPO3\Flow\Mvc\Exception\InvalidArgumentNameException
	 */
	public function executeFlowCommandWithJsonResponse($command) {
		$request = $this->requestBuilder->build($command);
		$response = new \TYPO3\Flow\Cli\Response();
		$this->dispatcher->dispatch($request, $response);
		return json_decode($response->getContent(), TRUE);
	}

	/**
	 * Moves TYPO3 Flow's autoloader to the top of the SPL stack.
	 *
	 * @throws \TYPO3\Flow\Exception
	 */
	protected function promoteAutoloading() {
		$classLoader = $this->bootstrap->getEarlyInstance('TYPO3\Flow\Core\ClassLoader');
		spl_autoload_unregister(array($classLoader, 'loadClass'));
		spl_autoload_register(array($classLoader, 'loadClass'), TRUE, TRUE);
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

}

