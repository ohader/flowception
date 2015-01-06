<?php
namespace Codeception\Extension;

use Codeception\Exception\Extension as ExtensionException;

class Flowception extends \Codeception\Platform\Extension {

	/**
	 * @var \TYPO3\Flow\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var FlowceptionRequestHandler
	 */
	protected $requestHandler;

	public function __construct($config, $options) {
		parent::__construct($config, $options);

		if (!isset($config['rootPath'])) {
			$config['rootPath'] = '.';
		}
		if (!is_dir(realpath($config['rootPath']))) {
			throw new ExtensionException($this, 'TYPO3 Flow root path is not valid "' . realpath($config['rootPath']) . '"');
		}

		$_SERVER['FLOW_ROOTPATH'] = realpath($config['rootPath']);
		$context = !empty($config['context']) ? trim($config['context']) :  'Testing';

		$this->writeLn("\n");
		$this->writeLn('Initializing TYPO3 Flow with context ' . $context);

		$this->bootstrap = new \TYPO3\Flow\Core\Bootstrap($context);
		$this->requestHandler = new FlowceptionRequestHandler($this->bootstrap);
		$this->bootstrap->registerRequestHandler($this->requestHandler);
		$this->bootstrap->setPreselectedRequestHandlerClassName('Codeception\\Extension\\FlowceptionRequestHandler');

		$this->requestHandler->setCommand(array('help'));

		$this->startFlow();
	}

	protected function startFlow() {
		$this->writeLn('Running TYPO3 Flow');
		$this->bootstrap->run();
	}

	protected function stopFlow() {
		$this->writeLn('Stopping TYPO3 Flow');
		$this->bootstrap->shutdown('Runtime');
	}

}

