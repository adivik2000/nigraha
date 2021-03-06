<?php
/* SVN FILE: $Id: dispatcher.php 5422 2007-07-09 05:23:06Z phpnut $ */
/**
 * Dispatcher takes the URL information, parses it for paramters and
 * tells the involved controllers what to do.
 *
 * This is the heart of Cake's operation.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision: 5422 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-07-09 00:23:06 -0500 (Mon, 09 Jul 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * List of helpers to include
 */
	uses('router', DS.'controller'.DS.'controller');
/**
 * Dispatcher translates URLs to controller-action-paramter triads.
 *
 * Dispatches the request, creating appropriate models and controllers.
 *
 * @package		cake
 * @subpackage	cake.cake
 */
class Dispatcher extends Object {
/**
 * Base URL
 *
 * @var string
 * @access public
 */
	var $base = false;
/**
 * Current URL
 *
 * @var string
 * @access public
 */
	var $here = false;
/**
 * Admin route (if on it)
 *
 * @var string
 * @access public
 */
	var $admin = false;
/**
 * Webservice route
 *
 * @var string
 * @access public
 */
	var $webservices = null;
/**
 * Plugin being served (if any)
 *
 * @var string
 * @access public
 */
	var $plugin = null;
/**
 * Constructor.
 */
	function __construct() {
		parent::__construct();
	}
/**
 * Dispatches and invokes given URL, handing over control to the involved controllers, and then renders the results (if autoRender is set).
 *
 * If no controller of given name can be found, invoke() shows error messages in
 * the form of Missing Controllers information. It does the same with Actions (methods of Controllers are called
 * Actions).
 *
 * @param string $url	URL information to work on.
 * @param array $additionalParams	Settings array ("bare", "return"),
 * which is melded with the GET and POST params.
 * @return boolean		Success
 * @access public
 */
	function dispatch($url, $additionalParams = array()) {
		$params = array_merge($this->parseParams($url), $additionalParams);
		$missingController = false;
		$missingAction = false;
		$missingView = false;
		$privateAction = false;
		$this->base = $this->baseUrl();

		if (empty($params['controller'])) {
			$missingController = true;
		} else {
			$ctrlName = Inflector::camelize($params['controller']);
			$ctrlClass = $ctrlName.'Controller';

			if (!loadController($ctrlName)) {
				$pluginName = Inflector::camelize($params['action']);
				if (!loadController($ctrlName . '.' . $pluginName)) {
					if (preg_match('/([\\.]+)/', $ctrlName)) {
						Router::setRequestInfo(array($params, array('base' => $this->base, 'webroot' => $this->webroot)));

						return $this->cakeError('error404',
														array(array('url' => strtolower($ctrlName),
																'message' => 'Was not found on this server',
																'base' => $this->base)));
					} elseif (!class_exists($ctrlClass)) {
						$missingController = true;
					} else {
						$params['plugin'] = null;
						$this->plugin = null;
					}
				} else {
					$params['plugin'] = Inflector::underscore($ctrlName);
				}
			} else {
				$params['plugin'] = null;
				$this->plugin = null;
			}
		}

		if (isset($params['plugin'])) {
			$plugin = $params['plugin'];
			$pluginName = Inflector::camelize($params['action']);
			$pluginClass = $pluginName.'Controller';
			$ctrlClass = $pluginClass;
			$oldAction = $params['action'];
			$params = $this->_restructureParams($params);
			$this->plugin = $plugin;
			loadPluginModels($plugin);
			$this->base = $this->base.'/'.Inflector::underscore($ctrlName);

			if (empty($params['controller']) || !class_exists($pluginClass)) {
				$params['controller'] = Inflector::underscore($ctrlName);
				$ctrlClass = $ctrlName.'Controller';
				if (!is_null($params['action'])) {
					array_unshift($params['pass'], $params['action']);
				}
				$params['action'] = $oldAction;
			}
		}

		if (empty($params['action'])) {
			$params['action'] = 'index';
		}

		if (defined('CAKE_ADMIN')) {
			if (isset($params[CAKE_ADMIN])) {
				$this->admin = '/'.CAKE_ADMIN ;
				$url = preg_replace('/'.CAKE_ADMIN.'(\/|$)/', '', $url);
				$params['action'] = CAKE_ADMIN.'_'.$params['action'];
			} elseif (strpos($params['action'], CAKE_ADMIN) === 0) {
				$privateAction = true;
			}
		}
		$base = Router::stripPlugin($this->base, $this->plugin);
		if (defined('BASE_URL')) {
			$this->here = $base . $this->admin . $url;
		} else {
			$this->here = $base . $this->admin . '/' . $url;
		}

		if ($missingController) {
			Router::setRequestInfo(array($params, array('base' => $this->base, 'webroot' => $this->webroot)));
			return $this->cakeError('missingController', array(
				array(
					'className' => Inflector::camelize($params['controller']."Controller"),
					'webroot' => $this->webroot,
					'url' => $url,
					'base' => $this->base
				)
			));
		} else {
			$controller =& new $ctrlClass();
		}

		$classMethods = get_class_methods($controller);
		$classVars = get_object_vars($controller);

		if ((in_array($params['action'], $classMethods) || in_array(strtolower($params['action']), $classMethods)) && strpos($params['action'], '_', 0) === 0) {
			$privateAction = true;
		}

		if (!in_array($params['action'], $classMethods) && !in_array(strtolower($params['action']), $classMethods)) {
			$missingAction = true;
		}

		if (in_array(strtolower($params['action']), array(
			'object', 'tostring', 'requestaction', 'log', 'cakeerror', 'constructclasses', 'redirect', 'set', 'setaction',
			'validate', 'validateerrors', 'render', 'referer', 'flash', 'flashout', 'generatefieldnames',
			'postconditions', 'cleanupfields', 'beforefilter', 'beforerender', 'afterfilter', 'disablecache', 'paginate'))) {
			$missingAction = true;
		}

		if (in_array('return', array_keys($params)) && $params['return'] == 1) {
			$controller->autoRender = false;
		}

		$controller->base = $this->base;
		$controller->here = $this->here;
		$controller->webroot = $this->webroot;
		$controller->params = $params;
		$controller->action = $params['action'];

		if (!empty($controller->params['data'])) {
			$controller->data =& $controller->params['data'];
		} else {
			$controller->data = null;
		}

		$namedArgs = array();
		if (is_array($controller->namedArgs)) {
			if (array_key_exists($params['action'], $controller->namedArgs)) {
				$namedArgs = $controller->namedArgs[$params['action']];
			} else {
				$namedArgs = $controller->namedArgs;
			}
			$controller->namedArgs = true;
		}
		if (!empty($controller->params['pass'])) {
			$controller->passedArgs =& $controller->params['pass'];
			if ($controller->namedArgs === true) {
				$controller->namedArgs = array();
				$c = count($controller->passedArgs);
				for ($i = 0; $i <= $c; $i++) {
					if (isset($controller->passedArgs[$i]) && strpos($controller->passedArgs[$i], $controller->argSeparator) !== false) {
						list($argKey, $argVal) = explode($controller->argSeparator, $controller->passedArgs[$i]);
						if (empty($namedArgs) || (!empty($namedArgs) && in_array($argKey, array_keys($namedArgs)))) {
							$controller->passedArgs[$argKey] = $argVal;
							$controller->namedArgs[$argKey] = $argVal;
							unset($controller->passedArgs[$i]);
							unset($params['pass'][$i]);
						}
					} elseif ($controller->argSeparator === '/') {
						$ii = $i + 1;
						if (isset($controller->passedArgs[$i]) && isset($controller->passedArgs[$ii])) {
							$argKey = $controller->passedArgs[$i];
							$argVal = $controller->passedArgs[$ii];
							if (empty($namedArgs) || (!empty($namedArgs) && in_array($argKey, array_keys($namedArgs)))) {
								$controller->passedArgs[$argKey] = $argVal;
								$controller->namedArgs[$argKey] = $argVal;
								unset($controller->passedArgs[$i], $controller->passedArgs[$ii]);
								unset($params['pass'][$i], $params['pass'][$ii]);
							}
						}
					}
				}
				$controller->passedArgs = am($namedArgs, $controller->passedArgs);
				$controller->namedArgs = am($namedArgs, $controller->namedArgs);
			}
		} else {
			$controller->passedArgs = null;
			if ($controller->namedArgs === true) {
				$controller->passedArgs = array();
				$controller->namedArgs = array();
				$controller->passedArgs = am($namedArgs, $controller->passedArgs);
				$controller->namedArgs = am($namedArgs, $controller->namedArgs);
			}
		}

		if (!empty($params['bare'])) {
			$controller->autoLayout = !$params['bare'];
		}

		$controller->webservices = $params['webservices'];
		$controller->plugin = $this->plugin;
		if (isset($params['viewPath'])) {
			$controller->viewPath = $params['viewPath'];
		}
		if (isset($params['layout'])) {
			if ($params['layout'] === '') {
				$controller->autoLayout = false;
			} else {
				$controller->layout = $params['layout'];
			}
		}

		foreach (array('components', 'helpers') as $var) {
			if (isset($params[$var]) && !empty($params[$var]) && is_array($controller->{$var})) {
				$diff = array_diff($params[$var], $controller->{$var});
				$controller->{$var} = array_merge($controller->{$var}, $diff);
			}
		}

		if (!is_null($controller->webservices)) {
			array_push($controller->components, $controller->webservices);
			array_push($controller->helpers, $controller->webservices);
		}
		Router::setRequestInfo(array($params, array('base' => $this->base, 'here' => $this->here, 'webroot' => $this->webroot, 'passedArgs' => $controller->passedArgs, 'argSeparator' => $controller->argSeparator, 'namedArgs' => $controller->namedArgs, 'webservices' => $controller->webservices)));
		$controller->_initComponents();
		$controller->constructClasses();

		if ($missingAction && !in_array('scaffold', array_keys($classVars))) {
			$this->start($controller);
			return $this->cakeError('missingAction', array(
				array(
					'className' => Inflector::camelize($params['controller']."Controller"),
					'action' => $params['action'],
					'webroot' => $this->webroot,
					'url' => $url,
					'base' => $this->base
				)
			));
		}

		if ($privateAction) {
			$this->start($controller);
			return $this->cakeError('privateAction', array(
				array(
					'className' => Inflector::camelize($params['controller']."Controller"),
					'action' => $params['action'],
					'webroot' => $this->webroot,
					'url' => $url,
					'base' => $this->base
				)
			));
		}

		return $this->_invoke($controller, $params, $missingAction);
	}
/**
 * Invokes given controller's render action if autoRender option is set. Otherwise the
 * contents of the operation are returned as a string.
 *
 * @param object $controller Controller to invoke
 * @param array $params Parameters with at least the 'action' to invoke
 * @param boolean $missingAction Set to true if missing action should be rendered, false otherwise
 * @return string Output as sent by controller
 * @access protected
 */
	function _invoke (&$controller, $params, $missingAction = false) {
		$this->start($controller);
		$classVars = get_object_vars($controller);

		if ($missingAction && in_array('scaffold', array_keys($classVars))) {
			uses('controller'. DS . 'scaffold');
			return new Scaffold($controller, $params);
		} else {
			$output = call_user_func_array(array(&$controller, $params['action']), empty($params['pass'])? null: $params['pass']);
		}
		if ($controller->autoRender) {
			$output = $controller->render();
		}
		$controller->output =& $output;

		foreach ($controller->components as $c) {
			$path = preg_split('/\/|\./', $c);
			$c = $path[count($path) - 1];
			if (isset($controller->{$c}) && is_object($controller->{$c}) && is_callable(array($controller->{$c}, 'shutdown'))) {
				if (!array_key_exists('enabled', get_object_vars($controller->{$c})) || $controller->{$c}->enabled == true) {
					$controller->{$c}->shutdown($controller);
				}
			}
		}
		$controller->afterFilter();
		return $controller->output;
	}
/**
 * Starts up a controller (by calling its beforeFilter methods and
 * starting its components)
 *
 * @param object $controller Controller to start
 * @access public
 */
	function start(&$controller) {
		if (!empty($controller->beforeFilter)) {
			if (is_array($controller->beforeFilter)) {

				foreach ($controller->beforeFilter as $filter) {
					if (is_callable(array($controller,$filter)) && $filter != 'beforeFilter') {
						$controller->$filter();
					}
				}
			} else {
				if (is_callable(array($controller, $controller->beforeFilter)) && $controller->beforeFilter != 'beforeFilter') {
					$controller->{$controller->beforeFilter}();
				}
			}
		}
		$controller->beforeFilter();

		foreach ($controller->components as $c) {
			$path = preg_split('/\/|\./', $c);
			$c = $path[count($path) - 1];
			if (isset($controller->{$c}) && is_object($controller->{$c}) && is_callable(array($controller->{$c}, 'startup'))) {
				if (!array_key_exists('enabled', get_object_vars($controller->{$c})) || $controller->{$c}->enabled == true) {
					$controller->{$c}->startup($controller);
				}
			}
		}
	}

/**
 * Returns array of GET and POST parameters. GET parameters are taken from given URL.
 *
 * @param string $fromUrl	URL to mine for parameter information.
 * @return array Parameters found in POST and GET.
 * @access public
 */
	function parseParams($fromUrl) {
		$Route = Router::getInstance();
		extract(Router::getNamedExpressions());
		include CONFIGS.'routes.php';
		$params = Router::parse($fromUrl);

		if (ini_get('magic_quotes_gpc') == 1) {
			if (!empty($_POST)) {
				$params['form'] = stripslashes_deep($_POST);
			}
		} else {
			$params['form'] = $_POST;
		}

		if (isset($params['form']['data'])) {
			$params['data'] = Router::stripEscape($params['form']['data']);
			unset($params['form']['data']);
		}

		if (isset($_GET)) {
			if (ini_get('magic_quotes_gpc') == 1) {
				$url = stripslashes_deep($_GET);
			} else {
				$url = $_GET;
			}
			if (isset($params['url'])) {
				$params['url'] = am($params['url'], $url);
			} else {
				$params['url'] = $url;
			}
		}

		foreach ($_FILES as $name => $data) {
			if ($name != 'data') {
				$params['form'][$name] = $data;
			}
		}

		if (isset($_FILES['data'])) {
			foreach ($_FILES['data'] as $key => $data) {
				foreach ($data as $model => $fields) {
					foreach ($fields as $field => $value) {
						$params['data'][$model][$field][$key] = $value;
					}
				}
			}
		}
		$params['bare'] = empty($params['ajax'])? (empty($params['bare'])? 0: 1): 1;
		$params['webservices'] = empty($params['webservices']) ? null : $params['webservices'];
		return $params;
	}
/**
 * Returns a base URL.
 *
 * @return string	Base URL
 * @access public
 */
	function baseUrl() {
		$htaccess = null;
		$base = $this->admin;
		$this->webroot = '';

		if (defined('BASE_URL')) {
			$base = BASE_URL.$this->admin;
		}

		$docRoot = env('DOCUMENT_ROOT');
		$scriptName = env('PHP_SELF');
		$r = null;
		$appDirName = str_replace('/','\/',preg_quote(APP_DIR));
		$webrootDirName = str_replace('/', '\/', preg_quote(WEBROOT_DIR));

		if (preg_match('/'.$appDirName.'\\'.DS.$webrootDirName.'/', $docRoot)) {
			$this->webroot = '/';

			if (preg_match('/^(.*)\/index\.php$/', $scriptName, $r)) {

				if (!empty($r[1])) {
					return  $base.$r[1];
				}
			}
		} else {
			if (defined('BASE_URL')) {
				$webroot = setUri();
				$htaccess = preg_replace('/(?:'.APP_DIR.'\\/(.*)|index\\.php(.*))/i', '', $webroot).APP_DIR.'/'.$webrootDirName.'/';
			}

			if (preg_match('/^(.*)\\/'.$appDirName.'\\/'.$webrootDirName.'\\/index\\.php$/', $scriptName, $regs)) {

				if (APP_DIR === 'app') {
					$appDir = null;
				} else {
					$appDir = '/'.APP_DIR;
				}
				!empty($htaccess)? $this->webroot = $htaccess : $this->webroot = $regs[1].$appDir.'/';
				return  $base.$regs[1].$appDir;

			} elseif (preg_match('/^(.*)\\/'.$webrootDirName.'([^\/i]*)|index\\\.php$/', $scriptName, $regs)) {
				!empty($htaccess)? $this->webroot = $htaccess : $this->webroot = $regs[0].'/';
				return  $base.$regs[0];

			} else {
				!empty($htaccess)? $this->webroot = $htaccess : $this->webroot = '/';
				return $base;
			}
		}
		return $base;
	}
/**
 * Restructure params in case we're serving a plugin.
 *
 * @param array $params Array on where to re-set 'controller', 'action', and 'pass' indexes
 * @return array Restructured array
 * @access protected
 */
	function _restructureParams($params) {
		$params['controller'] = $params['action'];

		if (isset($params['pass'][0])) {
			$params['action'] = $params['pass'][0];
			array_shift($params['pass']);
		} else {
			$params['action'] = null;
		}
		return $params;
	}
}

?>