<?php
/*
	Plugin Name: CODN Gateway for WP e-Commerce
	Plugin URI: http://codnusantara.com/
	Description: Adds the CODN payment option into WP e-Commerce. CODN is a Cashless on Delivery payment gateway. <strong>If you need support, please <a href="http://codnusantara.com/pengaduan-codnusantara/" target="_blank">contact CODN</a>.</strong>
	Version: 1.0
	Author: CODN Development Team
	Author URI: http://codnusantara.com
 */

       if (!defined('ABSPATH')) {
	exit;
}

if (!defined('CODN_PAYMENTS_PLUGIN_ROOT')) {
	define('CODN_PAYMENTS_PLUGIN_ROOT', dirname(__FILE__) . '/');
	define('CODN_PAYMENTS_PLUGIN_FILE', __FILE__);
	define('CODN_PAYMENTS_PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
	define('CODN_PAYMENTS_VERSION', '1.0');
}

/**
* autoload classes as/when needed
* @param string $class_name name of class to attempt to load
*/
function codn_payments_autoload($class_name) {
	static $classMapPlugin = array (
		// application classes
		'CODNPaymentsPlugin' => 'includes/class.CODNPaymentsPlugin.php',

		// integrations
		'CODNPaymentsWpsc' => 'includes/integrations/class.CODNPaymentsWpsc.php',
	);

	if (isset($classMapPlugin[$class_name])) {
		require CODN_PAYMENTS_PLUGIN_ROOT . $classMapPlugin[$class_name];
	}
}
spl_autoload_register('codn_payments_autoload');

// initialise plugin
CODNPaymentsPlugin::getInstance();
	
        function configForm() {
        // Get stored values.
        
        $publickey = get_option('codn_publickey');
        $privatekey = get_option('codn_privatekey');
        $notifyurl = get_option('codn_notifyurl');
        $returnurl = get_option('codn_returnurl');
        $keepbasket = get_option('codn_keepbasket');
        $mailerrors = get_option('codn_mailerrors');


        // Merchant ID.
        $output .= '<tr><td><label for="codn_publickey">Merchant CODN Public Key</label></td>';
        $output .= '<td><input name="wpsc_options[codn_publickey]" id="codn_publickey" type="text" value="' . $publickey . '"/><br/>';
        $output .= wpeccodn_form_hint('This is your CODN account public key.');
        $output .= '</td></tr>';

        // Merchant ID.
        $output .= '<tr><td><label for="codn_privatekey">Merchant CODN Private Key</label></td>';
        $output .= '<td><input name="wpsc_options[codn_privatekey]" id="codn_privatekey" type="text" value="' . $privatekey . '"/><br/>';
        $output .= wpeccodn_form_hint('This is your CODN account private key.');
        $output .= '</td></tr>';

        // Merchant ID.
        $output .= '<tr><td><label for="codn_returnurl">Return URL </label></td>';
        $output .= '<td><input name="wpsc_options[codn_returnurl]" id="codn_returnurl" type="text" value="' . $returnurl . '"/><br/>';
        $output .= wpeccodn_form_hint('This is return url after check out.');
        $output .= '</td></tr>';

        // Merchant ID.
        $output .= '<tr><td><label for="codn_notifyurl">Notify URL</label></td>';
        $output .= '<td><input name="wpsc_options[codn_notifyurl]" id="codn_notifyurl" type="text" value="' . $notifyurl . '"/><br/>';
        $output .= wpeccodn_form_hint('This is notify url for payment confirmation.');
        $output .= '</td></tr>';

        return $output;
    }

	function wpeccodn_form_hint($s)
	{
		return '<small style="line-height:14px;display:block;padding:2px 0 6px;">' . $s . '</small>';
	}
        
        function _wpsc_filter_codn_merchant_customer_notification_raw_message( $message, $notification ) {
            
            global $wpdb;
            $sql = 'select * from `' . WPSC_TABLE_PURCHASE_LOGS . '` where sessionid = %s limit 1';
            $purchase_logs = $wpdb->get_row($wpdb->prepare($sql, $_GET['sessionid']), ARRAY_A);
            $original_cart_data = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid` = {$purchase_logs['id']}", ARRAY_A );
            $checkout_form = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `active` = 1 ", ARRAY_A );
            
            $purchase_log = $notification->get_purchase_log();
            
            // Prepare Parameters
            $chart = array();
            $data_billing = array();
            foreach ( $original_cart_data as $item ) {
                $metas = get_post_meta($item['prodid'],'_wpsc_product_metadata');
                $weight = $metas[0]['weight']/2.20462;
                $chart[] = array($item['name'],$item['price'],$item['quantity'],1,1,1,round($weight));//array(nama,harga, quantity, panjang, lebar, tinggi, berat) 
            }
            
            foreach($checkout_form as $form){
                $names = array('billingfirstname','billinglastname','billingemail','billingphone');
                if(in_array($form['unique_name'], $names)){
                    $submit_form = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `form_id` = {$form['id']} AND `log_id` = {$purchase_logs['id']} ", ARRAY_A );
                    $data_billing[] = $submit_form[0]['value'];
                }
            }
            
            $returnurl = get_option('codn_returnurl');
            $notifyurl = get_option('codn_notifyurl');
            
            if(empty($returnurl)){
                $returnurl = 'http://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            }else{
                $returnurl = get_option('codn_returnurl');
            }
            
            if(empty($notifyurl)){
                $notifyurl = 'http://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            }else{
                $notifyurl = get_option('codn_notifyurl');
            }
            
           //echo '<pre>';print_r($data_billing);echo '</pre>';
            
            $params = array(
                'public_key'                => get_option('codn_publickey'),                                   
                'buyer_name'                => $data_billing[0].' '.$data_billing[1] ,                             
                'buyer_email'               => $data_billing[2],                        
                'buyer_phone'               => $data_billing[3],                                   
                'order_id'                  => $purchase_logs['id'],
                'product'                   => $chart,
                'return_url'                => $returnurl, //akan di arahkan kehalaman ini setelah proses di payment page COD selesai           
                'notify_url'                => $notifyurl, //tujuan callback CODN (method POST) parameter bisa dilihat di file notify.php             
            );

            $data = base64_encode(json_encode($params));
            $signature = base64_encode( sha1( get_option('codn_privatekey') . $data . get_option('codn_privatekey') ) );
            //echo '<pre>';print_r($_POST);echo '</pre>';
            

            
		$message = '<img src="'.plugins_url('/images/pay-now.png',__FILE__).'" style="cursor:pointer;margin:auto;display:block;" class="codn_button" codn_data="'.$data.'" codn_signature="'.$signature.'">';
                return $message;
        }

add_filter(
	'wpsc_purchase_log_customer_notification_raw_message',
	'_wpsc_filter_codn_merchant_customer_notification_raw_message',
	10,
	2
);
add_filter(
	'wpsc_purchase_log_customer_html_notification_raw_message',
	'_wpsc_filter_codn_merchant_customer_notification_raw_message',
	10,
	2
);

function codn_scripts() {
        wp_enqueue_style( 'codn_css', 'http://app.codnusantara.com/pay/codn.css');
        wp_register_script( 'codn_js', plugins_url('/includes/js/codn.js',__FILE__), array(), '1.0.0', true );
        wp_enqueue_script( 'codn_js' );
        wp_localize_script('codn_js', 'WPURLS', array( 'imgurl' => plugins_url('/images/close.png',__FILE__) ));
}
add_action( 'wp_enqueue_scripts', 'codn_scripts' );

?>