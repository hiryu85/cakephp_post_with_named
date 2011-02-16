# CakePHP PostWithNamed
A CakePHP plugin for posting a form inputs in URL (example http://domain/cakephp/controller/action/search:something/category_id:44) with named parameters.

## Installation

* Clone/Copy the files in this directory into `app/plugins/post_with_named`
* Add the component to your controller:
   * `var $components = array('PostWithNamed');`
* Register into the components options the urls allowed for post in url in this mode:
  * `var $components = array(
        'PostWithNamed.PostWithNamed' => array(
            'process' => array(
               # Put here all allowed urls
               #  (All CakePHP url format is allowed. Example: /foo/bar or array('controller' => 'foo', 'action' => 'bar')
               '/search/index',
                array('controller' => 'your-controller-name-here', 'action' => 'your-action-name-here'),
                ),
   )