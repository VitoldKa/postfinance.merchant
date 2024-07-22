<?php
/* Postfinance merchant for WP e-Commerce
 * autor : Kapshitzer Vitold
 * copyright 2013
 */
 
/**
 * It uses the wpsc_merchant class as a base class which is handy for collating user details and cart contents.
 */

define("AUTHORIZED",5);
define("PAYMENT_REQUESTED",9);
define("INVALID_OR_INCOMPLETE",0);
define("AUTHORIZATION_REFUSED",2);
define("AUTHORIZATION_WAITING",51);
define("PAYMENT_PROCESSING",91);
define("AUTHORIZATION_NOT_KNOWN",52);
define("PAYMENT_UNCERTAIN",92);
define("PAYMENT_REFUSED",93);
 
 /*
  * This is the gateway variable $nzshpcrt_gateways, it is used for displaying gateway information on the wp-admin pages and also
  * for internal operations.
  */
$nzshpcrt_gateways[$num] = array(
	'name' =>  __( 'PostFinance  Checkout', 'wpsc' ),
	'internalname' => 'wpsc_merchant_postfinance',
	'display_name' => __( 'PostFinance', 'wpsc' ),
	'function' => 'gateway_postfinance',
	'form' => "form_postfinance",
	'submit_function' => "submit_postfinance"	
);



function gateway_postfinance($seperator, $sessionid){
//$wpdb is the database handle,
//$wpsc_cart is the shopping cart object

global $wpdb, $wpsc_cart;
//print_r($wpsc_cart);
//This grabs the purchase log id from the database
//that refers to the $sessionid
$purchase_log = $wpdb->get_row(
"SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS.
"` WHERE `sessionid`= ".$sessionid." LIMIT 1"
,ARRAY_A) ;
//print_r($purchase_log);
//This grabs the users info using the $purchase_log
// from the previous SQL query

$usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value,
`".WPSC_TABLE_CHECKOUT_FORMS."`.`name`,
`".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM
`".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN
`".WPSC_TABLE_SUBMITED_FORM_DATA."` ON
`".WPSC_TABLE_CHECKOUT_FORMS."`.id =
`".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE
`".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$purchase_log['id'];

//echo("\n".$usersql);
$userinfo = $wpdb->get_results($usersql, ARRAY_A);
$wpdb->print_error();
//echo ($collected_gateway_data);
$user_data = $wpdb->get_results("SELECT `id`,`type` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` WHERE `type` IN ('email') AND `active` = '1'",ARRAY_A);
//print_r($userinfo);
//Now we will store all the information into an associative array
//called $data to prepare it for sending via cURL
//echo( wpsc_get_customer_meta( 'billingfirstname' ));

foreach((array)$userinfo as $key => $value){
//print_r($value);
$data[$value["unique_name"]] = $value["value"];
}

$data = array(
	// <!-- general parameters -->
//	'PARAMPLUS' => 'postfinance_callback=true&sessionid='.$sessionid,
	'CN' => $data['billingfirstname'].' '.$data['billinglastname'],
	'OWNERADDRESS' => $data['billingaddress'],
	'AMOUNT' => number_format($wpsc_cart->total_price,2,'',''),
	'CURRENCY' => 'CHF', // USD
	'LANGUAGE' => 'en_US',
	'ORDERID' => $sessionid,
	'PSPID' => get_option( 'postfinance_pspid'),
	'EMAIL' => $data['billingemail'],
	'OWNERZIP' => $data['billingpostcode'],
	'OWNERCTY' => $data['billingcountry'],
	'OWNERTELNO' => $data['billingphone'],
	'OWNERTOWN' => $data['billingcity'],
	// <!-- apparence et impression: voir Apparence de la page de paiement -->
	'TITLE' => '',
	'BGCOLOR' => '',
	'TXTCOLOR' => '',
	'TBLBGCOLOR' => '',
	'TBLTXTCOLOR' => '',
	'BUTTONBGCOLOR' => '',
	'BUTTONTXTCOLOR' => '',
	'LOGO' => '',
	'FONTTYPE' => '',
	// <!--redirection après la transaction : voir Feedback au client sur la transaction -->
	'ACCEPTURL' => get_option( 'transact_url' )."&sessionid=".$sessionid
//	'DECLINEURL' => get_option( 'transact_url' )."&sessionid=".$sessionid,
//	'EXCEPTIONURL' => get_option( 'transact_url' )."&sessionid=".$sessionid,
//	'CANCELURL' => get_option( 'transact_url' )."&sessionid=".$sessionid
//	'CANCELURL' => urlencode($_SERVER["HTTP_ORIGIN"].$_SERVER["REQUEST_URI"])
);
// do_action('wpsc_transaction_results');
//please note that the key in the array may need to be changed 
//to work with your gateway (refer to your gateways documentation).
/*
AMOUNT=1500Mysecretsig1875!?CURRENCY=EURMysecretsig1875!?
LANGUAGE=en_USMysecretsig1875!?ORDERID=1234Mysecretsig1875!?
PSPID=MyPSPIDMysecretsig1875!?
*/
$shakeyin= get_option( 'postfinance_sha_in') ;


//print_r($data);

// print the postfinance hidden form
$output = '<table border="0" style="width:100%;height:100%;text-align:center"><tr><td>
<form method="post" action="'. get_option( 'postfinance_gateway_url') .'"
id=form1 name=form1>';

foreach($data as $key => $value){
	if(strlen($value) > 0){
		$output .='<input type="hidden" name="'.$key.'" value="'.utf8_encode($value).'">'."\n";
	}
}

// <!-- vérification avant le paiement : voir: Sécurité : vérification avant le paiement -->
$data2 = array_change_key_case($data, CASE_UPPER);
ksort($data2);
foreach($data2 as $key => $value){
	if(isset($data2[$key]) && strlen($data2[$key])>0){
		$hashkey .= strtoupper($key).'='.utf8_encode($data2[$key]).$shakeyin;
	}
}
$shakey = strtoupper(sha1($hashkey));
$output .= '<input type="hidden" name="SHASIGN" value="'. $shakey .'">
<input type="submit" style="font-size:24px" value="Continue to payment" id=submit2 name="Continue to payment">
</form>';
//echo ("\n".$hashkey."\n");

// Message de redirection vers postfinance

$output .= "<script language=\"javascript\" type=\"text/javascript\">document.getElementById('form1').submit();</script>";
$output .= "</td></tr></table>";


echo($output);
// wp_redirect( $payPalURL );
  	exit();
}

/* form_postfinance()
Gateway URL : https://e-payment.postfinance.ch/ncol/test/orderstandard.asp
PSPID
SHA-IN/SHA-OUT
Additional fee
License Key
Additional Parameters
*/
function form_postfinance(){

$output.="
<tr>
<td>PSPID</td>
<td>
<input type='text' size='40' value='" . get_option( 'postfinance_pspid') . "' name='postfinance_pspid' />
</td>
</tr>

<tr>
<td>Gateway URL</td>
<td>
<input type='text' size='120' value='" . get_option( 'postfinance_gateway_url') . "' name='postfinance_gateway_url' />
</td>
</tr>

<tr>
<td>SHA-IN</td>
<td>
<input type='text' size='40' value='" . get_option( 'postfinance_sha_in') . "' name='postfinance_sha_in' />
</td>
</tr>

<tr>
<td>SHA-OUT</td>
<td>
<input type='text' size='40' value='" . get_option( 'postfinance_sha_out') . "' name='postfinance_sha_out' />
</td>
</tr>
";
/*
<tr>
<td>Additional fee</td>
<td>
<input type='text' size='40' value='" . get_option( 'postfinance_add_fee') . "' name='postfinance_add_fee' />
</td>
</tr>

<tr>
<td>License Key</td>
<td>
<input type='text' size='40' value='" . get_option( 'postfinance_license_key') . "' name='postfinance_license_key' />
</td>
</tr>

<tr>
<td>Additional Parameters</td>
<td>
<input type='text' size='40' value='" . get_option( 'postfinance_extra_parameters') . "' name='postfinance_extra_parameters' />
</td>
</tr> */


return $output;

}
/*
postfinance_pspid
postfinance_gateway_url
postfinance_sha_in_out
postfinance_add_fee
postfinance_license_key
postfinance_extra_parameters
*/
function submit_postfinance(){

	if ( isset ( $_POST['postfinance_pspid'] ) ) {
	update_option( 'postfinance_pspid', $_POST['postfinance_pspid'] );
	}
	if ( isset ( $_POST['postfinance_gateway_url'] ) ) {
	update_option( 'postfinance_gateway_url', $_POST['postfinance_gateway_url'] );
	}
	if ( isset ( $_POST['postfinance_sha_in'] ) ) {
	update_option( 'postfinance_sha_in', $_POST['postfinance_sha_in'] );
	}
	if ( isset ( $_POST['postfinance_sha_out'] ) ) {
	update_option( 'postfinance_sha_out', $_POST['postfinance_sha_out'] );
	}
	
/*	if ( isset ( $_POST['postfinance_add_fee'] ) ) {
	update_option( 'postfinance_add_fee', $_POST['postfinance_add_fee'] );
	}
	if ( isset ( $_POST['postfinance_license_key'] ) ) {
	update_option( 'postfinance_license_key', $_POST['postfinance_license_key'] );
	}
	if ( isset ( $_POST['postfinance_extra_parameters'] ) ) {
	update_option( 'postfinance_extra_parameters', $_POST['postfinance_extra_parameters'] );
	}*/

return true;

}

function nzshpcrt_postfinance_callback()
{
global $wpdb, $wpsc_cart;

//syslog(LOG_INFO, "nzshpcrt_postfinance_callback");
$data = array(
				'orderID' =>'',
				'amount' =>'',
				'currency' =>'',
				'PM' =>'',
				'ACCEPTANCE' =>'',
				'STATUS' =>'',
				'CARDNO' =>'',
				'PAYID' =>'',
				'NCERROR' =>'',
				'BRAND' =>'',
				'ED' =>'',
				'TRXDATE' =>'',
				'CN' =>'',
				'IP' => '',
				'VC' => '',
				'AAVCheck' => '',
				'CVCCheck' => '',
				'ECI' => '',
				'CCCTY' => '',
				'IPCTY' => '',
				'BRAND' => '',
				'NCERROR' => ''
);
// postfinance_callback=true&sessionid='.$sessionid
//				'postfinance_callback'	 => ''			
//				'sessionid' => '',

//print_r($_POST);
$shakeyout= get_option( 'postfinance_sha_out') ;

foreach($data as $key => $value){
	if(isset($_POST[$key]) && strlen($_POST[$key])>0){
		$data[$key]=$_POST[$key];
//		$hashkey .= strtoupper($key).'='.$_POST[$key].$shakeyout;
//		syslog(LOG_INFO, $key ." = ".$data[$key]);
	}
}

$data2 = array_change_key_case($data, CASE_UPPER);
ksort($data2);
foreach($data2 as $key => $value){
	if(isset($data2[$key]) && strlen($data2[$key])>0){
		$hashkey .= strtoupper($key).'='.$data2[$key].$shakeyout;
	}
}


//$hashkey .= "\n";
$shakey = strtoupper(sha1($hashkey));
//$shakey = 123;
//syslog(LOG_INFO, "shakeyout = ".$shakeyout);
//syslog(LOG_INFO, "hashkey = ".$hashkey);
//syslog(LOG_INFO, "shakey1 = ".$shakey);
//exit();

//print_r($_GET);
//print_r($data);
foreach($_POST as $key => $value){
//			syslog(LOG_INFO, "callback::_POST: ".$key." = ". $value);
}
foreach($_GET as $key => $value){
//			syslog(LOG_INFO, "callback::_GET: ".$key." = ". $value);
}
// isset($_POST['postfinance_callback']) && ($_POST['postfinance_callback'] == 'true') && 
       if(($_POST['SHASIGN'] == $shakey))
{
	$sessionid = $data['orderID'];

	$purchase_log_sql = $wpdb->prepare( "SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= %s LIMIT 1", $sessionid );
	$purchase_log = $wpdb->get_results($purchase_log_sql,ARRAY_A) ;
	$cart_sql = "SELECT * FROM `".WPSC_TABLE_CART_CONTENTS."` WHERE `purchaseid`='".$purchase_log[0]['id']."'";
	$cart = $wpdb->get_results($cart_sql,ARRAY_A) ;

//print_r($data);

//echo('$sessionid'.$sessionid."\n");
//echo('$data["PAYID"]'.$data["PAYID"]."\n");
//			syslog(LOG_INFO, "Callback");
	switch($data["STATUS"]) {
		case AUTHORIZED:
		case PAYMENT_REQUESTED:
				wpsc_update_purchase_log_details(
					$data["orderID"],
						array(
							'processed' => 3,
							'date' => time(),
							'transactid' => $data["PAYID"],
							'authcode' => $data["ACCEPTANCE"]
						),
						'sessionid'
					);
//					wpsc_update_purchase_log_details( $_POST["sessionid"],,);
//			wpsc_update_purchase_log_status( $_POST["sessionid"], 3, 'sessionid' );
			transaction_results( $sessionid, false);
//			syslog(LOG_INFO, "Callback::AUTHORIZED");
		break;
		case AUTHORIZATION_WAITING:
		case PAYMENT_UNCERTAIN:
		case PAYMENT_PROCESSING:
		case AUTHORIZATION_NOT_KNOWN:
				wpsc_update_purchase_log_details(
					$data["orderID"],
						array(
							'processed' => 2,
							'date' => time(),
							'transactid' => $data["PAYID"],
							'authcode' => $data["ACCEPTANCE"]
						),
						'sessionid'
					);
//	    	syslog(LOG_INFO, "Callback::AUTHORIZATION_WAITING");
			break;
		case PAYMENT_REFUSED:
		case AUTHORIZATION_REFUSED:
				wpsc_update_purchase_log_details(
					$data["orderID"],
						array(
							'processed' => 6,
							'date' => time(),
							'transactid' => $data["PAYID"],
							'authcode' => $data["ACCEPTANCE"]
						),
						'sessionid'
					);
//	    	syslog(LOG_INFO, "Callback::PAYMENT_REFUSED");
			break;
		
		}
		$wpsc_cart->empty_cart();
//		echo('$data["orderID"] = '. $data["orderID"]);
		add_query_arg( 'postfinance_callback', 'true', home_url( '/' ) );
//		transaction_results($data["orderID"],true, );
		exit();
	 
	}

}

function nzshpcrt_postfinance_results()
{
	if(isset($_POST['orderID']) && ($_POST['orderID'] !='') && ($_GET['sessionid'] == ''))
	{
		$_GET['sessionid'] = $_POST['orderID'];
	}
}

add_action('init', 'nzshpcrt_postfinance_callback');
add_action('init', 'nzshpcrt_postfinance_results');
