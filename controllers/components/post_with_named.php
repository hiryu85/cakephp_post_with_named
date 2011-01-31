<?php
/**
 * Post form data in named parameters mode
 * 
 * 
 * Convert all elements into your form from POST ($this->data) in named 
 * parameters (foo:bar) in $this->params['named'] __if it rappresent a model field (Car.name)__
 * otherwise it will be stored into Session named PostWithNamed 
 * -$this->Session->read('PostWithNamed.inputs') in your controller-.
 * 
 * 
 *  === This component can be initialized with this options ===
 * 
 *  "process" => array(url1 [, url2..] )    
 *      Url can be in format "/controllers/action" or 
 *      array('controller' => 'foo', 'action' => 'bar')
 *
 *   "sanitize" => boolean [default=true] 
 *      Use cakephplib Sanitize::pranoid()
 * 
 *   "encode"   => boolean [default=true]
 *      Encode post value with urlencode
 * 
 * 
 * @version     0.2-alpha
 * @author      Mirko Chialastri <m.chialastri@gmai.com>
 * 
 **/
class PostWithNamedComponent extends Object {

    /**
     * CakePHP Component options
     */
    var $components = array('Session');

    /**
     * Internal variables
     */
    var $__version = '0.1';
    var $__settings = array(
        'process' => array(),
        'sanitize' => true,
        'encode' => true,
    );
    var $__isActive = FALSE;
    
    
    
	//called before Controller::beforeFilter()
	function initialize(&$controller, $settings = array()) {
		$this->controller =& $controller;
        $this->__settings = array_merge($this->__settings, $settings);
	}

	//called after Controller::beforeFilter()
	function startup(&$controller) {        
        if (empty($this->controller->params['named']) && $this->controller->referer() == '/') { 
             $this->controller->Session->delete('PostWithNamed.inputs');
             return;
        }
        
        /*
         * This determine if current url is in "process" array, if is in
         * array the component can process the request otherwise don't do anything.
         * 
         */
        foreach($this->__settings['process'] as $index => $cakeURL) {
            if ($this->controller->here == Router::url($cakeURL)) { 
                $this->__isActive = TRUE;
                break;
            }
        }
        
        // This url not in processable url's (in "process" options key)
        if ($this->__isActive === FALSE) return;
        // Direct access, nothing to redirect with named parameters.
        /*debug(empty($this->controller->params['named']));
        debug($this->controller->referer());
        */
        
        
        /*
         * Foreach on $this->controller->data for create an named arguments
         * for redirecting the visitator to new url (post2named).
         * 
         * This create an array like this:
         *      array(
         *          'Model.field' => 'value',
         *           ... 
         *      )
         *  
         */ 
        App::import('Sanitize');
        $vars = array();
        $inputs = array();
        
        // Init session (clear last inputs)
        $this->controller->Session->write('PostWithNamed.inputs', false);
        
        foreach($this->controller->data as $model => &$fields) {
            // Like $this->data[$model] = value, maybe is a input that
            // not rappresent a model (<input name="[data][something]" value="foo" />) 
            if (!is_array($fields)) {
                debug($fields);
                $tmp = $this->__settings['satanize'] ? Sanitize::paranoid($fields) : $fields;
                $tmp = $this->__settings['encode'] ? urlencode($tmp) : $tmp;
                $inputs[$model]  = $tmp;
                $vars[$model] = $tmp;
                continue;
            }
            // Key could be a model (in form [data][model][field] = value)
            foreach($fields as $key => &$value) { 
                $tmp = $this->__settings['satanize'] ? Sanitize::paranoid($value) : $value;
                $tmp = $this->__settings['encode'] ? urlencode($tmp) : $tmp;
                $vars[$model.'.'.$key] = $tmp;
            }
        }
        debug($vars);
        // Save unknowed inputs to a session
        $this->controller->Session->write('PostWithNamed.inputs', $inputs);

        $redirect = array_merge(array(
            'controller' => $this->controller->params['controller'],
            'action' => $this->controller->params['action'],
        ), $vars);
        
        // Redirect to current url but with named vars and 302 http status
        $this->controller->redirect($redirect, 302, true);
	}
    

    
	//called before Controller::redirect()
	function beforeRedirect(&$controller, $url, $status=null, $exit=true) {
	}



    /**
     * When you use this component for submitting forms elements in
     * named format may be useful to pre-fill in the form of inputs 
     * with the value passed by name, this does not happen if you do not 
     * save in $this->data value and this function allows recreate the 
     * array starting with the named parameters.
     * 
     * Example: $this->data = $this->PostWithNamed->named2data();
     */
    function named2data() { 
        $named_args = array_keys($this->controller->params['named']);
        preg_match_all('/((?P<model>\w+)\.(?P<field>\w+)|(?P<input>\w+)),?/i', 
                       join(',', $named_args), $nameds, PREG_SET_ORDER);
        
        foreach($nameds as $i => $matches) {
            // Is model rappresentation (format: Post.author)
            if ($matches['model'] && $matches['field']) {
                $model = $matches['model'];
                $field = $matches['field'];                
                $this->controller->data[$model][$field] = $this->controller->params['named']["$model.$field"];
            } else {
                // Is only a input field :D
                $input = $matches['input'];
                $this->controller->data[$input] = $this->controller->params['named']["$input"];
            } 
        }
        // Insert all non-model inputs into session
        $this->controller->data[0]  = $this->controller->Session->read('PostWithNamed.inputs');
    }
    
}
?>
