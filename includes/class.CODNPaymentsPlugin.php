<?php

/**
* plugin controller class
*/
class CODNPaymentsPlugin {
	public $urlBase;									// string: base URL path to files in plugin

	/**
	* static method for getting the instance of this singleton object
	* @return CODNPaymentsPlugin
	*/
	public static function getInstance() {
		static $instance = NULL;

		if (is_null($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	* initialise plugin
	*/
	private function __construct() {
		$this->urlBase = plugin_dir_url(CODN_PAYMENTS_PLUGIN_FILE);

		// register with WP e-Commerce
		add_filter('wpsc_merchants_modules', array($this, 'wpscRegister'));
	}
        
	/**
	* register new WP e-Commerce payment gateway
	* @param array $gateways array of registered gateways
	* @return array
	*/
	public function wpscRegister($gateways) {
		return CODNPaymentsWpsc::register($gateways);
	}


}
