<?php
$webhook_url="https://www.softwarehomework.com/ipn_webhook.php";
$site_name="GSM";

// $_POST=unserialize(file_get_contents("paypal_tmp"));

$post_string="";
foreach($_POST as $field=>$value)
  $post_string .= $field . '=' . urlencode(stripslashes($value)) . '&';
$post_string.="&xlsx_site=".urlencode(stripslashes($site_name));
echo "$post_string\n";
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
$result=curl_exec($ch);
echo $result;
curl_close($ch);


?>
