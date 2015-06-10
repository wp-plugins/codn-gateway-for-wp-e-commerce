<?php

/**
 * payment gateway integration for WP e-Commerce
 * @link http://docs.getshopped.org/category/developer-documentation/
 */


class CODNPaymentsWpsc extends wpsc_merchant{
    
    public $name = 'codn';
    private $object;
    const WPSC_GATEWAY_NAME = 'wpsc_merchant_codn';
    
    
    
    /**
     * register new payment gateway
     * @param array $gateways array of registered gateways
     * @return array
     */
    public static function register($gateways) {
        // register the gateway class and additional functions
        $gateways[] = array (
                'name'				=> 'CODN payment gateway',
                'api_version'			=> 2.0,
                'image'				=> plugins_url('../../images/logo_codn.png',__FILE__),
                'internalname'			=> self::WPSC_GATEWAY_NAME,
                'class_name'			=> __CLASS__,
                'has_recurring_billing'		=> false,
                'wp_admin_cannot_cancel'	=> true,
                'display_name'			=> 'CODN Payment',
                'form'                          => 'configForm',		// called as variable function name, wp-e-commerce is _doing_it_wrong(), again!
                'submit_function'               => array(__CLASS__, 'saveConfig'),
                'payment_type'			=> 'transfer',
                'requirements'			=> array('php_version' => 5.2,),
        );
        
        return $gateways;
    }

    /**
     * submit to gateway
     */
	public function submit() {
            global $wpdb;
            $this->set_purchase_processed_by_purchid(2);
            $this->go_to_transaction_results($this->cart_data['session_id']);

            exit();
	 	
	}


    /**
     * save config details from payment gateway admin
     */
    public static function saveConfig() {

        $publickey = sanitize_text_field($_POST['codn_publickey']);
        $privatekey = sanitize_text_field($_POST['codn_privatekey']);
        $returnurl = sanitize_text_field($_POST['codn_returnurl']);
        $notifyurl = sanitize_text_field($_POST['codn_notifyurl']);
        
        if (isset($publickey)){
        update_option('codn_publickey', $publickey);}

        if (isset($privatekey)){
        update_option('codn_privatekey', $privatekey);}

        if (isset($returnurl)){
        update_option('codn_returnurl', $returnurl);}

        if (isset($notifyurl)){
        update_option('codn_notifyurl', $notifyurl);}
        
        return true;
        
        
    }

}

