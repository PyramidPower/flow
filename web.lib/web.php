<?php
// $Id: web.php 938 2010-11-24 19:22:29Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

define("CHUNK_SIZE", 1024*1024); // Size (in bytes) of tiles chunk

class PermissionDeniedException extends Exception {
	
}

/**
 * A class for simple processing of web requests like http://webpy.org
 * Author 2007 Carsten Eckelmann
 */
class Web {

    var $_buffer;
    var $_logLevel;
    var $_logHandlers;
    var $_template;
    var $_templatePath;
    var $_templateExtension;
    var $_url;
    var $_context;
    var $_logMethod;
    var $_logParam;
    var $_logFolder = "log";
    var $_logFile = "web";
    var $_logExt = ".log";
    var $_logLevelArray;
    var $_action;
    var $_actionParam;
    var $_handlerParam;
    var $_defaultHandler;
    var $_defaultAction;
    var $_layoutContentMarker;
    var $_notFoundTemplate;
    var $_layout;
    var $_headers;
    var $_preHandlers;
    var $_postHandlers;
    var $_handler = null;
    var $_subhandler = null;
    var $_handlerPath;
    var $_handlerExtension;
    var $_handlers;
    var $_requestMethod;
    var $_action_executed = false;
    var $_action_redirected = false;
    var $_services;
    var $_moduleConfig;
    var $_paths;
    var $_handlerLibExtension;
    var $_loginpath = 'auth/login';

    var $db;
    
    /**
     * @var AuthService
     */
    var $auth;

    /**
     * Constructor
     */
    function __construct() {
    	session_name("FLOW_SID");
        session_start();
        $this->_logMethod = "file";
        $this->_logParam = "web.log";
        $this->_logFolder = "log";
        $this->_logFile = "web";
        $this->_logExt = ".log";
        $this->_logLevel = 0;
        $this->_buffer = null;
        $this->_context = array();
        $this->_templatePath = "templates";
        $this->_templateExtension = ".tpl.php";
        $this->_handlerLibExtension= ".lib.php";
        $this->_template = null;
        $this->_logLevelArray = array("debug","info","warn","audit","error");
        $this->_handlerParam = "handler";
        $this->_handlerExtension = ".actions.php";
        $this->_action = null;
        $this->_actionParam = "action";
        $this->_defaultHandler = "main";
        $this->_defaultAction = "index";
        $this->_layoutContentMarker = "body";
        $this->_notFoundTemplate = "404";
        $this->_paths = null;
        $this->_services = array();
        $this->_layout = "layout";
        $this->_headers = null;
        $this->_preHandlers= null;
        $this->_handler = null;
        $this->_subhandler = null;
        $this->_handlerPath = "modules";
        $this->_webroot = "/";
        $this->_actionMethod = null;
        spl_autoload_register(array($this, 'modelLoader'));
    }
    
    private function modelLoader($className) {
        $handlers = $this->handlers();
        foreach ($handlers as $model) {
            $file = $this->getHandlerDir($model).'model/'.ucfirst($className).".class.php";
            if (file_exists($file)) {
                include $file;
                $this->logDebug("Class ".$file." loaded.");
                return true;
            }
        }
        $this->logDebug("Class ".$file." NOT FOUND.");
        return false;
    }
    
    /**
     * Thanks to:
     * http://www.phpaddiction.com/tags/axial/url-routing-with-php-part-one/
     */
    private function & _getCommandPath() {
    	$this->logDebug("REQUEST_URI: ".$_SERVER['REQUEST_URI']);
        $uri = explode('?',$_SERVER['REQUEST_URI']);// get rid of parameters
        $uri = $uri[0];
        // get rid of trailing slashes
        if (substr($uri, -1) == "/") {
            $uri = substr($uri,0,-1);
        }
        $requestURI = explode('/', $uri);
        $scriptName = explode('/',$_SERVER['SCRIPT_NAME']);
        for($i= 0;$i < sizeof($scriptName);$i++) {
            if ($requestURI[$i] == $scriptName[$i]) {
                unset($requestURI[$i]);
            }
        }
        return array_values($requestURI);
    }

    /**
     * start processing of request
     * 1. look at the request parameter if the action parameter was set
     * 2. if not set, look at the pathinfo and use first
     */
    function start() {
        //$this->debug("Start processing: ".$_SERVER['REQUEST_URI']);

        // find out which handler to use
        $handler_found = false;
        $action_found = false;

        $this->_paths = $this->_getCommandPath();
        //$this->_webroot = dirname($_SERVER['SCRIPT_NAME']);

        // first find the handler file
        if ($_REQUEST[$this->_handlerParam] && $this->_paths == null) {
            $this->_handler = $_REQUEST[$this->_handlerParam];
        } else if ($this->_paths && sizeof($this->_paths) > 0) {
            $this->_handler = array_shift($this->_paths);
        }

        // then find the action
        if ($_REQUEST[$this->_actionParam] && $this->_paths == null) {
            $this->_action = $_REQUEST[$this->_actionParam];
        } else if ($this->_paths && sizeof($this->_paths) > 0) {
            $this->_action = array_shift($this->_paths);
        }

        if (! $this->_handler) {
            $this->_handler = $this->_defaultHandler;
        }

        // see if the handler is a sub handler
        // eg. /sales-report/showreport/1..
        $hsplit = explode("-",$this->_handler);
        $this->_handler = array_shift($hsplit);
        $this->_subhandler = array_shift($hsplit);

        if (! $this->_action) {
            $this->_action = $this->_defaultAction;
        }

        // try to load a handler file
        if (!$this->_subhandler) {
        	// try to load single action file first
        	// /<handler>/<version>/actions/<action>.action.php
        	$dirpath = $this->getHandlerDir($this->_handler).'/actions/'.$this->_action.'.action.php';
        	if (file_exists($dirpath)) {
        		$reqpath = $dirpath;
        	} else {
            	$reqpath = $this->getHandlerDir($this->_handler).$this->_handler.$this->_handlerExtension;
        	}
        } else {
        	// try to load single action file first
        	// /<handler>/<version>/actions/<subhandler>/<action>.action.php
        	$dirpath = $this->getHandlerDir($this->_handler).'/actions/'.$this->_subhandler.'/'.$this->_action.'.action.php';
        	if (file_exists($dirpath)) {
        		$reqpath = $dirpath;
        	} else {
        	// if a subhandler has been requested then the file to load is
            // /<handler>/<version>/<handler>.<subhandler>.<handlerextension>
            	$reqpath = $this->getHandlerDir($this->_handler).$this->_handler.".".$this->_subhandler.$this->_handlerExtension;
        	}
        }
        
        //
        // if a handler file for this url exists, then start processing
        //
        if (file_exists($reqpath)) {
	        $this->context('webroot', $this->_webroot);
	        $this->context('handler',$this->_handler);
	        $this->context('subhandler',$this->_handler);
	        $this->context('action',$this->_action);
        	
	        // Load all models from all modules
	        // this needs to be done before checking access
	        // in order to load the Auth Module
	        $this->_loadModels();
        	
	        // CHECK ACCESS!!
	        $this->_initAuth();
	        $this->checkAccess(); // will redirect if access denied!
        	        	
            // try to load the central lib file for this handler if it exists
            $reqlibpath = $this->getHandlerDir($this->_handler).$this->_handler.$this->_handlerLibExtension;
            if (file_exists($reqlibpath)) {
                require_once $reqlibpath;
            }
            
            // load the handler file
            require_once $reqpath;
        } else {
            $this->logError("Handler not found: ".$reqpath);
            $this->notFoundPage();
        }

        // try to find action for the request type
        // using <handler>_<action>_<type>()
        // or just <action>_<type>()
        
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        $actionmethods[] = $this->_handler.'_'.$this->_action.'_'.$this->_requestMethod;
        $actionmethods[] = $this->_handler.'_'.$this->_action.'_ALL';
        $actionmethods[] = $this->_action.'_'.$this->_requestMethod;
        $actionmethods[] = $this->_action.'_ALL';
        
        foreach ($actionmethods as $action_method) {
            if (function_exists($action_method)) {
        	    $action_found = true;
            	$this->_actionMethod = $action_method;
            	break;
            }
        }
        
        if ($action_found) {
            $this->ctx("loggedIn",$this->auth->loggedIn());
            $this->ctx("error",$_SESSION['error']);
            unset($_SESSION['error']);
            $this->ctx("msg",$_SESSION['msg']);
            unset($_SESSION['msg']);
            $this->ctx("w",$this);

            
            
            
            try{
	            // Call all registered PRE handlers
	            $this->_callPreHandlers();
	
	            // Load all listeners and call PRE ACTION listeners
	            $this->_loadListeners();
	            $this->_callPreListeners();
	
	            // Execute the action
	            $method = $this->_actionMethod;
	            //$this->logDebug("EXECUTE ACTION ==> ".$method);
	            $this->_action_executed = true;
	            $method($this);
	
	            // Call all POST ACTION listeners
	            // INFO: These will also be called in the
	            // redirect method!
	            $this->_callPostListeners();
	
	            // Call all registered POST handlers
	            // INFO: These will also be called in the
	            // redirect method!
	            $this->_callPostHandlers();
            
            } catch (PermissionDeniedException $ex) { 
            	$this->error($ex->getMessage());
            } 
//            catch (Exception $e){
//            	
//            	// The Error will be shown on the user default page == Inbox.
//            	$info = $e->getMessage()."<br>";
//            	$info.= str_replace('#','<br>#',$e->getTraceAsString());
//            	$this->error($info);
//            }
            
            

            // send headers first
            if ($this->_headers) {
                foreach ($this->_headers as $key => $val) {
                    header($key.': '.$val);
                }
            }
            $body = null;
            // evaluate template only when buffer is empty
            if (sizeof($this->_buffer) == 0) {
                $body = $this->fetchTemplate();
            } else {
                $body = $this->_buffer;
            }
            // but always check for layout
            // if ajax call don't do the layout
            if ($this->_layout && !$this->isAjax()) {
                $this->_buffer = null;
                $this->context($this->_layoutContentMarker,$body);
                $this->templateOut($this->_layout);
            } else {
                $this->_buffer = $body;
            }
            echo $this->_buffer;
        } else {
            $this->notFoundPage();
        }
        exit(); // nothing comes after start()!!!
    }

    private function _initAuth() {
        $this->auth = $this->service("Auth");
    }

    public function __get($name) {
		if ($name == ucfirst($name)) {
			return $this->service($name);
		}	
    }
    
    function setModules($modules) {
        $this->_moduleConfig = $modules;
    }

    /**
     * Read Module configuration values
     * 
     * @param <type> $module
     * @param <type> $key
     * @return <type>
     */
    function moduleConf($module,$key) {
        if (array_key_exists($module, $this->_moduleConfig)
                && array_key_exists($key, $this->_moduleConfig[$module])) {
            return $this->_moduleConfig[$module][$key];
        } else {
            return null;
        }
    }
    
    function isAjax() {
    	return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
    /**
     * Check if the currently logged in user
     * has access to this path
     *
     * @param <type> $msg
     * @return <type>
     */
    function checkAccess($msg="Access Restricted") {
    	$subaction = $this->_subhandler ? "-".$this->_subhandler : "";
        $path = $this->_handler.$subaction."/".$this->_action;
        if ($this->auth && $this->auth->user()) {
            $user = $this->auth->user();
            $usrmsg = $user ? " for ".$user->login : "";
            if (!$this->auth->allowed($path)) {
                $this->logInfo("Access Denied to ".$path.$usrmsg." from ".$this->requestIpAddress());
                // redirect to the last allowed page 
                $this->error($msg,$_SESSION['LAST_ALLOWED_URI']); 
            }
        } else if ($this->auth 
        		&& !$this->auth->loggedIn() 
        		&& $path != $this->_loginpath 
        		&& !$this->auth->allowed($path)) {
			$_SESSION['orig_path']=$_SERVER['REQUEST_URI'];
			$this->redirect($this->localUrl($this->_loginpath));        	
        }
        // Saving the last allowed uri so we can
        // redirect to it from a failed call
        if (!$this->isAjax()) {
	    	$_SESSION['LAST_ALLOWED_URI'] = $_SERVER['REQUEST_URI'];
        }
        return true;
    }

    /**
     * 
     * Return the mimetype for a file path
     * @param $filename (including path)
     * @return string
     */
    function getMimetype($filename) {
        $mime = "text/html";
        if (function_exists(finfo_open)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
            $mime = finfo_file($finfo, $filename);
            finfo_close($finfo);
        } else {
            ob_start();
            system("file -i -b {$filepath}");
            $output = ob_get_clean();
            $output = explode("; ",$output);
            if ( is_array($output) ) {
                $output = $output[0];
            }
            $mime = $output;
        }
        return $mime;
    }

    /**
     * Send the contents of the file to the client browser
     * as raw data.
     * 
     * @param string $filename
     */
    function sendFile($filename) {
        $this->logInfo("Trying to load file: ".$filename);
        if (!file_exists($filename)) {
            $this->logWarn("Could not load file: ".$filename);
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        $mimetype = $this->getMimetype($filename);

        header('Content-Type: '.$mimetype );
        $buffer = '';
        $cnt =0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, CHUNK_SIZE);
            echo $buffer;
            ob_flush();
            flush();
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }

        exit;

    }

    /**
     * Convenience Method for creating menu's
     * This will check if $path is allowed
     * and will then return an html link or nothing
     *
     * if $array is set will also add the link to the array
     *
     * @param string $path
     * @param string $title
     * @param array $array
     * @return string
     */
    function menuLink($path,$title,&$array=null) {
        $link =$this->auth->allowed($path,Html::a($this->localUrl($path),$title));
        if ($array !== null) {
            $array[]=$link;
        }
        return $link;
    }

    /**
     * Same as menuLink but displays a button instead
     * @param string $path
     * @param string $title
     * @param string $array
     * @return string html code
     */
    function menuButton($path,$title,&$array=null) {
        $link =$this->auth->allowed($path,Html::b($this->localUrl($path),$title));
        if ($array !== null) {
            $array[]=$link;
        }
        return $link;
    }

    /**
     * Convenience Method for creating menu's
     * This will check if $path is allowed
     * and will then return an html link or nothing
     *
     * This will create a link which will open a popup box
     *
     * if $array is set will also add the link to the array
     *
     * @param string $path
     * @param string $title
     * @param array $array
     */
    function menuBox($path,$title,&$array=null) {
        $link =$this->auth->allowed($path,Html::box($this->localUrl($path),$title));
        if ($array !== null) {
            $array[]=$link;
        }
        return $link;
    }

    /**
     * Creates a url prefixed with the webroot
     *
     * @param string $link
     * @return string html code
     */
    function localUrl($link=null) {
        if (strpos($link, "/") !== 0) {
            $link = "/".$link;
        }
        return $this->webroot().$link;
    }

    /**
     * Redirect to $url and display an
     * error message
     *
     * @param <type> $msg
     * @param <type> $url
     */
    function error($msg,$url="") {
        $_SESSION['error']=$msg;
        $this->ctx('error',$msg);
        $this->redirect($this->localUrl($url));
    }

    /**
     * Redirect to $url and display
     * a message
     *
     * @param <type> $msg
     * @param <type> $url
     */
    function msg($msg,$url="") {
        $_SESSION['msg']=$msg;
        $this->ctx('msg',$msg);
        $this->redirect($this->localUrl($url));
    }

    /**
     * Sends 404 header and displays not found message<br/>
     * <b>THIS EXITS the current process</b>
     */
    function notFoundPage() {
        $this->logWarn("Action not found: ".$this->_handler."/".$this->_action);
        if ($this->templateExists($this->_notFoundTemplate)) {
            echo $this->fetchTemplate($this->_notFoundTemplate);
        }
        else {
            header("HTTP/1.0 404 Not Found");
            echo '<p align="center">Sorry, page not found.</p>';
        }
        exit();
    }

    function internalLink($title,$handler,$action=null,$params=null) {
        if (!$this->auth->allowed($handler,$action)) {
            return null;
        } else {
            return "<a href='".$this->localUrl("/".$handler."/".$action.$params)."'>".$title."</a>";
        }
    }

    /**
     * Return all handlers currently in the codebase
     */
    function handlers() {
        return array_keys($this->_moduleConfig);
    }

    function getHandlerDir($handler) {
        return $this->_handlerPath."/".$handler."/".$this->moduleConf($handler,"version")."/";
    }

    /**
     * Return all actions for a particular handler
     *
     * @param <type> $handler
     * @return <type>
     */
    function actions($handler) {
        $actions = array();
        $handlerfile = $this->getHandlerDir($handler).$this->_handlerExtension;
        if (file_exists($handlerfile)) {
            $handle = @fopen($handlerfile, "r");
            if ($handle) {
                while (!feof($handle)) {
                    $buffer = fgets($handle, 4096);
                    if (preg_match("/^\s*function\s*".$handler."_(\w+)_(GET|POST|ALL)\s*\(.*$/", $buffer, $matches)) {
                        $actions[]=$matches[1];
                    }
                }
                fclose($handle);
            }
        }
        $actions = array_unique($actions);
        sort($actions);
        return $actions;
    }

    /**
     * Upon startup will load all models from all modules
     */
    function _loadModels() {
        $handlers = $this->handlers();
        foreach ($handlers as $model) {
            $file = $this->getHandlerDir($model).$model.".model.php";
            if (file_exists($file)) {
                require_once $file;
                //$this->logDebug("Model ".$file." loaded.");
            }
        }
    }

    /**
     * Return a preloaded Service as
     * defined in a model.php inside
     * as module.
     *
     * @param <type> $name
     * @return <type>
     */
    function & service($name) {
    	$name = ucfirst($name);
        if (!key_exists($name, $this->_services)) {
            $cname = $name."Service";
            if (class_exists($cname)) {
                $s = new $cname($this);
                // initialise
                if (method_exists($s, "__init")) {
                    $s->__init();
                }
                $this->_services[$name] = & $s;
            }
        }
        return $this->_services[$name];
    }

    function getHandlerPath() {
        return $this->_handlerPath;
    }
    
    
    /////////////////////////////////// Template stuff /////////////////////////

    function setLayout($l) {
        $this->_layout = $l;
    }

    function getLayout($l) {
        $this->_layout = $l;
    }

    function setTemplate($t) {
        $this->_template = $t;
    }

    function getTemplate() {
        return $this->_template;
    }

    /**
     * set the path where Web looks for template files
     */
    function setTemplatePath($path) {
        $this->_templatePath = $path;
    }

    function setTemplateExtension($ext) {
        $this->_templateExtension = $ext;
    }

    /**
     * check if a template file exists!
     */
    function templateExists($tmpl) {
        return file_exists($this->getTemplateRealFilename($tmpl));
    }

    function getTemplateRealFilename($tmpl) {
        return $tmpl.$this->_templateExtension;
    }

    /**
     * Evaluates a template in the web context and
     * returns it as string. The template is searched for
     * in the following order: <br/>
     * <pre>
     * /<handlerdir>/<handler>/<version>/templates/<subhandler>/<action>_<httpmethod>.tpl.php
     * /<handlerdir>/<handler>/<version>/templates/<subhandler>/<action>.tpl.php
     * /<handlerdir>/<handler>/<version>/templates/<subhandler>/<subhandler>.tpl.php
     * /<handlerdir>/<handler>/<version>/templates/<action>_<httpmethod>.tpl.php
     * /<handlerdir>/<handler>/<version>/templates/<action>.tpl.php
     * /<handlerdir>/<handler>/<version>/templates/<handler>.tpl.php
     * /<handlerdir>/<handler>/<version>/<action>_<httpmethod>.tpl.php
     * /<handlerdir>/<handler>/<version>/<action>.tpl.php
     * /<handlerdir>/<handler>/<version>/<handler>.tpl.php
     * /<templatedir>/<action>_<httpmethod>.tpl.php
     * /<templatedir>/<action>.tpl.php
     * /<templatedir>/<handler>.tpl.php
     * </pre>
     */
    function fetchTemplate($name=null) {
        if ($this->_subhandler) {
            $paths[] = implode("/",array($this->getHandlerDir($this->_handler),$this->_templatePath,$this->_subhandler));
        }
        $paths[] = implode("/",array($this->getHandlerDir($this->_handler),$this->_templatePath));
        $paths[] = implode("/",array($this->getHandlerDir($this->_handler)));
        $paths[] = implode("/",array($this->_templatePath,$this->_handler));
        $paths[] = $this->_templatePath;

        $names = array();
        if ($name) {
            $names[] = $name;
        } else {
            $names[] = $this->_actionMethod;
            $names[] = $this->_action;
            if ($this->_subhandler) {
                $names[] = $this->_subhandler;
            } else {
                $names[] = $this->_handler;
            }
        }

        // we need to find a template from a combination of paths and names
        // in the above arrays from the most specific to the most broad
        $template = null;
        foreach ($paths as $path) {
            foreach ($names as $nam) {
                if ($nam && $this->templateExists($path.'/'.$nam)) {
                    $template = $path.'/'.$nam;
                    break 2; // break out of both loops
                } else {
                    //$this->logDebug("no template @ ".$path.'/'.$nam);
                }
            }
        }

        if (!$template) {
            $this->logError("No Template found.");
            return null;
        }
        $tpl = & new Template();
        $tpl->set_vars($this->_context);
        return $tpl->fetch($this->getTemplateRealFilename($template));
    }

    /**
     * evaluate template and put the string into
     * the web context for inclusion in other
     * templates
     */
    function putTemplate($key, $template) {
        $this->context($key, $this->fetchTemplate($template));
    }

    /**
     * This will execute the passed in template
     * instead of the default one. The layout will
     * still be used!
     */
    function templateOut($template) {
        $this->out($this->fetchTemplate($template));
    }

    /**
     * prints to the page
     * if this is used, then the template will NOT be called
     * automatically! But the layout will still be used.
     */
    function out($txt) {
        $this->_buffer .= $txt;
    }


    function webroot() {
        return $this->_webroot;
    }

    /**
     * Turns a variable list of string arguments into
     * context entries loaded with the values of the url segments.
     *
     * eg: Given a URL with /one/two/three, calling
     *     pathMatch("eins","zwei","drei") will insert into the context
     *     ("eins" => "one", "zwei" => "two", "drei" => "three")
     *
     * @param multiple string params, which will be turned into ctx entries
     * @return an array of key, value pairs
     */
    function pathMatch() {
        $match = array();
        for($i=0;$i<func_num_args();$i++) {
            $param = func_get_arg($i);
            $val = urldecode($this->_paths[$i]);
            if (is_array($param)) {
                $key = $param[0];
                if (!$val) {
                    $val = $param[1];
                }
            } else {
                $key = $param;
            }
            $this->context($key,$val);
            $match[$key]=$val;
        }
        return $match;
    }

    /**
     * Returns the request value in a safe way
     * without generating warning.
     *
     * @param <type> $key
     * @param <type> $default
     * @return <type>
     */
    function request($key, $default=null) {
    	
    	// added on 18/03/2011. Serg
    	if(key_exists($key, $_REQUEST) && is_array($_REQUEST[$key]))
    	{
    		foreach ($_REQUEST[$key] as &$k)
    		{
    			urldecode($k);
    		}
    		return $_REQUEST[$key];
    	}
    	// end added
    	
        return key_exists($key, $_REQUEST) ? urldecode($_REQUEST[$key]) : $default;
    }

    function requestIpAddress() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Return the current handler
     * @return <type>
     */
    function currentHandler() {
        return $this->_handler;
    }

    /**
     * Return the current handler
     * @return <type>
     */
    function currentSubHandler() {
        return $this->_subhandler;
    }
    /**
     * Return the current Action
     */
    function currentAction() {
        return $this->_action;
    }

    /**
     * Set some handlers that are called BEFORE the real handler
     * is called.
     */
    function setPreHandlers() {
        if (func_num_args()) {
            $this->_preHandlers = func_get_args();
        }
    }
    /**
     * Set some handlers that are called AFTER the real handler
     * is called.
     */
    function setPostHandlers() {
        if (func_num_args()) {
            $this->_postHandlers = func_get_args();
        }
    }

    /**
     * Call all pre handlers.
     */
    function _callPreHandlers() {
        if ($this->_preHandlers) {
            foreach($this->_preHandlers as $h) {
                $handler_method = $h.'_PRE_'.$_SERVER['REQUEST_METHOD'];
                $handler_all = $h.'_PRE_ALL';

                if (function_exists($handler_method)) {
                    //$this->logDebug("Calling PRE handler: ".$handler_method);
                    $handler_method($this);
                } else if (function_exists($handler_all)) {
                    //$this->logDebug("Calling PRE handler: ".$handler_all);
                    $handler_all($this);
                }
            }
        }
    }

    /**
     * Call all post handlers.
     */
    function _callPostHandlers() {
        if ($this->_postHandlers) {
            foreach($this->_postHandlers as $h) {
                $handler_method = $h.'_POST_'.$_SERVER['REQUEST_METHOD'];
                $handler_all = $h.'_POST_ALL';

                if (function_exists($handler_method)) {
                    //$this->logDebug("Calling POST handler: ".$handler_method);
                    $handler_method($this);
                } else if (function_exists($handler_all)) {
                    //$this->logDebug("Calling POST handler: ".$handler_all);
                    $handler_all($this);
                }
            }
        }
    }

    function _loadListeners() {
        foreach ($this->handlers() as $h) {
            $lfile = $this->getHandlerDir($h).$h.".listeners.php";
            if (file_exists($lfile)) {
                require_once $lfile;
                //$this->logDebug("Listener ".$lfile." loaded.");
            }
        }
    }
    /**
     * Call all PRE ACTION listeners
     */
    function _callPreListeners() {
        foreach ($this->handlers() as $h) {
            $action = $h."_listener_PRE_ACTION";
            if (function_exists($action)) {
                //$this->logDebug("CALLING PRE listener: ".$action);
                $action($this);
            }
        }
    }


    /**
     * Call all POST ACTION listeners
     */
    function _callPostListeners() {
        foreach ($this->handlers() as $h) {
            $action = $h."_listener_POST_ACTION";
            if (function_exists($action)) {
                //$this->logDebug("CALLING POST listener: ".$action);
                $action($this);
            }
        }
    }

    /**
     * validates the request parameters according to
     * the rules passed in $valarray. It must be of the
     * following form:
     *
     * array(
     *   array("<param-name>","<regexp>","<error message>"),
     *   array("<param-name>","<regexp>","<error message>"),
     *   ...
     * )
     *
     * returns an array which contains all produced error
     * messages
     */
    function validate($valarray) {
        if (!$valarray || !sizeof($valarray)) return null;
        $error = array();
        foreach ($valarray as $rule) {
            $param = $rule[0];
            $regex = $rule[1];
            $message = $rule[2];
            $val = $_REQUEST[$param];
            if (!preg_match("/".$regex."/", $val)) {
                $error[]=$message;
            }
        }
        return $error;
    }

    /**
     * Return current request method
     * @return <type>
     */
    function currentRequestMethod() {
        return $this->_requestMethod;
    }

    /**
     * log functions
     */
    function logDebug($msg) {
        $this->_log(0,$msg);
    }
    function isDebug() {
    	return $this->_logLevel == 0;
    }
    function logInfo($msg) {
        $this->_log(1,$msg);
    }
    function isInfo() {
    	return $this->_logLevel <= 1;
    }
    function logWarn($msg) {
        $this->_log(2,$msg);
    }
    function isWarn() {
    	return $this->_logLevel <= 2;
    }
    function logAudit($msg) {
        $this->_log(3,$msg);
    }
    function isAudit() {
    	return $this->_logLevel <= 3;
    }
    function logError($msg) {
        $this->_log(4,$msg);
    }
    function isError() {
    	return $this->_logLevel <= 4;
    }
    
    function getPath() {
        return implode("/",$this->_paths);
    }
    /**
     * put a key value pair into the web context
     */
    function context($key, $value=null) {
        if ($value == null) {
            return $this->_context[$key];
        } else {
            $this->_context[$key] = $value;
        }
    }

    function ctx($key, $value=null) {
        return $this->context($key, $value);
    }

    /**
     * get/put a session value
     */
    function session($key,$value=null) {
        if ($value == null) {
            return $_SESSION[$key];
        } else {
            $_SESSION[$key] = $value;
        }
    }


    /**
     * Send a browser redirect
     */
    function redirect($url) {
        // stop endless loops!!
        if ($this->_action_redirected) {
            return;
        }
        $this->_action_redirected = true;
        $this->logDebug("Redirect: ".$url);

        // although we are redirecting we should
        // still call the POST handlers and listeners
        // but only if we got redirected from a real action
        // we don't want to call these if redirected from
        // a role check or pre handler/listener
        if ($this->_action_executed) {
            $this->_callPostListeners();
            $this->_callPostHandlers();
        }
        header("Location: ".$url);
        exit();
    }

    /**
     * set http header values
     */
    function sendHeader($key,$value) {
        $this->logDebug("Header[".$key."] = '".$value."'");
        $this->_headers[$key]=$value;
    }

    /**
     * returns a string representation of everything
     * session. request, url, headers, handlers,
     * template contexts. This can then be displayed on the page
     * or written to the log.
     */
    function dump() {
        echo "<pre>";
        echo "<b>========= WEB =========</b>";
        print_r($this);
        echo "<b>========= REQUEST =========</b>";
        print_r($_REQUEST);
        echo "<b>========= SESSION =========</b>";
        print_r($_SESSION);
        echo "</pre>";
    }

    function setLogLevel($levelstring) {
        $this->_logLevel = array_search($levelstring, $this->_logLevelArray);
    }

    function _log($level, $msg) {
        if ($level < $this->_logLevel) return;
        if ( $this->_logMethod == "file") {
            $this->_logToFile($level, $msg);
        }
    }

    function _logToFile($level, $msg) {
        //$f=fopen($this->_logParam, 'a');
        $f = fopen($this->_logFolder."/".$this->_logFile."-".date("Y-m-d").$this->_logExt,'a');
        fwrite($f, date('d/m/Y H:i:s').' '.strtoupper($this->_logLevelArray[$level]).' '.$msg."\n");
        fclose($f);
    }

}


///////////////////////////////////////////////////////////////////////////////
//                                                                           //
//                           Page Template System                            //
//                                                                           //
///////////////////////////////////////////////////////////////////////////////


class Template {
    var $vars; /// Holds all the template variables

    /**
     * Constructor
     *
     * @param string $path the path to the templates
     *
     * @return void
     */
    function Template() {
        $this->vars = array();
    }

    /**
     * Set a template variable.
     *
     * @param string $name name of the variable to set
     * @param mixed $value the value of the variable
     *
     * @return void
     */
    function set($name, $value) {
        $this->vars[$name] = $value;
    }

    /**
     * Set a bunch of variables at once using an associative array.
     *
     * @param array $vars array of vars to set
     * @param bool $clear whether to completely overwrite the existing vars
     *
     * @return void
     */
    function set_vars($vars, $clear = false) {
        if($clear) {
            $this->vars = $vars;
        }
        else {
            if(is_array($vars)) $this->vars = array_merge($this->vars, $vars);
        }
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param string string the template file name
     *
     * @return string
     */
    function fetch($file) {
        extract($this->vars);          // Extract the vars to local namespace
        ob_start();                    // Start output buffering
        include($file);  // Include the file
        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean();                // End buffering and discard
        return $contents;              // Return the contents
    }
}

/**
 * An extension to Template that provides automatic caching of
 * template contents.
 */
class CachedTemplate extends Template {
    var $cache_id;
    var $expire;
    var $cached;

    /**
     * Constructor.
     *
     * @param string $path path to template files
     * @param string $cache_id unique cache identifier
     * @param int $expire number of seconds the cache will live
     *
     * @return void
     */
    function CachedTemplate($path, $cache_id = null, $expire = 900) {
        $this->Template($path);
        $this->cache_id = $cache_id ? 'cache/' . md5($cache_id) : $cache_id;
        $this->expire   = $expire;
    }

    /**
     * Test to see whether the currently loaded cache_id has a valid
     * corrosponding cache file.
     *
     * @return bool
     */
    function is_cached() {
        if($this->cached) return true;

        // Passed a cache_id?
        if(!$this->cache_id) return false;

        // Cache file exists?
        if(!file_exists($this->cache_id)) return false;

        // Can get the time of the file?
        if(!($mtime = filemtime($this->cache_id))) return false;

        // Cache expired?
        if(($mtime + $this->expire) < time()) {
            @unlink($this->cache_id);
            return false;
        }
        else {
            /**
             * Cache the results of this is_cached() call.  Why?  So
             * we don't have to double the overhead for each template.
             * If we didn't cache, it would be hitting the file system
             * twice as much (file_exists() & filemtime() [twice each]).
             */
            $this->cached = true;
            return true;
        }
    }

    /**
     * This function returns a cached copy of a template (if it exists),
     * otherwise, it parses it as normal and caches the content.
     *
     * @param $file string the template file
     *
     * @return string
     */
    function fetch_cache($file) {
        if($this->is_cached()) {
            $fp = @fopen($this->cache_id, 'r');
            $contents = fread($fp, filesize($this->cache_id));
            fclose($fp);
            return $contents;
        }
        else {
            $contents = $this->fetch($file);

            // Write the cache
            if($fp = @fopen($this->cache_id, 'w')) {
                fwrite($fp, $contents);
                fclose($fp);
            }
            else {
                die('Unable to write cache.');
            }

            return $contents;
        }
    }
}


/**
 * License for Template and CachedTemplate classes:
 *
 * Copyright (c) 2003 Brian E. Lozier (brian@massassi.net)
 *
 * set_vars() method contributed by Ricardo Garcia (Thanks!)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */


