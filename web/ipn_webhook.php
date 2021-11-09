<?php
// added by kuldeep@fiverr
ini_set('memory_limit', -1);
// end of code by kuldeep
require_once("xlsxwriter.class.php");
require_once("SimpleXLSX.php");
ignore_user_abort(true);

ini_set("log_errors", 1);
ini_set("error_log", "php_log");
global $out;
$out=fopen("log_cdt", "a");


// $_POST=unserialize(file_get_contents("paypal_tmp"));
$obj = New PayPal_IPN();
$obj->ipn_response($_POST);

class PayPal_IPN {

  function ipn($request) {
  global $out;
  fwrite($out, "check ipn\n");
  	define('SSL_P_URL', 'https://www.paypal.com/cgi-bin/webscr');
  	define('SSL_SAND_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');

    fwrite($out, "POST\n");
    fwrite($out, serialize($request));
    fwrite($out, "\n\n");

  	$paypal_url = ($request['test_ipn'] == 1) ? SSL_SAND_URL : SSL_P_URL;
    fwrite($out, "$paypal_url\n");
  	$post_string = '';
  	foreach ($request as $field => $value) {
      if($field=="xlsx_site")
        continue;
  	  $post_string .= $field . '=' . urlencode(stripslashes($value)) . '&';
  	}
  	$post_string.="cmd=_notify-validate"; // append ipn command
  	// get the correct paypal url to post request to
    fwrite($out, "post - $post_string\n");
  	$ch = curl_init($paypal_url);

  	$ipn_response = '';

  	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  	curl_setopt($ch, CURLOPT_POST, 1);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
  	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
  	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  	curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
  	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
  	if ( !($ipn_response = curl_exec($ch)) ) {
  		// could not open the connection. If loggin is on, the error message
  		// will be in the log.
  		fwrite($out, "could not connect - ".curl_error($ch)."\n");
  		$ipn_status = "curl_error: ".curl_error($ch);
      return false;
  	}
  	curl_close($ch);
  	fwrite($out, "response: $ipn_response\n");
  	if (strcmp ($ipn_response, "VERIFIED") == 0)
  	{
  	$ipn_status = "IPN VERIFIED";
  fwrite($out, "verified\n");
  	if ($ipn_data == true) {
  		echo 'SUCCESS';
  		}

  		return true;
  	} else {

  	fwrite($out, "validation failed\n");
  	$ipn_status = 'IPN Validation Failed';

  	if ($ipn_data == true) {
  	echo 'Validation fail';
  	}
  	return false;
  			}
  }

  function ipn_response($request) {
    global $out;
    fwrite($out, print_r($request, true));
    fwrite($out, "\n\n");
    fwrite($out, serialize($request));
    fwrite($out, "\n\n");
    $res=$this->ipn($request);
    if($res)
      $this->insert_data($request);
  }

  function insert_data($request)
  {
    global $out;
    $data=[];
    $site=(isset($request["xlsx_site"])) ? $request["xlsx_site"] : "GSM";
    $rates=get_rates();
    if(stristr($request["payment_status"], "Revers"))
      return;
    if(isset($request["parent_txn_id"]))
      $request["txn_id"]=$request["parent_txn_id"];
    if(file_exists('paypalinfosample.xlsx'))
    {
      $rows=SimpleXLSX::parse('paypalinfosample.xlsx')->rowsEx();
      foreach($rows as $row)
      {
        if($row[1]["value"]=="")
          continue;
        $pts=explode(" ", $row[1]["value"]);
        if(count($pts)>1)
          $row[1]["value"]=str_replace("-", "/", $pts[0]);
        $pts=explode(" ", $row[2]["value"]);
        if(count($pts)>1)
          $row[2]["value"]=$pts[1];
        $block=array();
        $transaction_day["{$row[13]["value"]}"]=$row[1]["value"];
        foreach($row as $col)
          $block[]=$col["value"];
        // if(!isset($block[18]) or $block[18]=="")
        {
          // $block[18]=$rates["$block[7]"];
          // // echo "$block[7]: {$rates[$block[7]]}\n";
          // if($block[18]==0 or $block[18]=='')
          //   $block[19]=0;
          // else
          //   $block[19]=$block[10]/$block[18];
        }
        if(is_nan($block[19]))
          $block[19]=0;
        $m_pts=explode("/", $block[1]);
        $date_str="$m_pts[2]/$m_pts[1]/$m_pts[0]";
        $month=date("Y/m/d:F, d", strtotime("$date_str 00:00:01"));
        if(isset($transaction_day["{$request["txn_id"]}"]))
        {
          $m_pts=explode("/", $transaction_day["{$request["txn_id"]}"]);
          $month=date("Y/m/d:F, d", strtotime("$m_pts[2]/$m_pts[1]/$m_pts[0] 01:01:01"));
        }
        if(!isset($monthly["overall"]["$month"]))
          $monthly["overall"]["$month"]=0;
        $monthly["overall"]["$month"]+=$block[19];
        if(!isset($monthly["products"]["$month"]["$block[16]"]))
        {
          $monthly["products"]["$month"]["$block[16]"]["qty"]=0;
          $monthly["products"]["$month"]["$block[16]"]["total"]=0;
        }
        $monthly["products"]["$month"]["$block[16]"]["qty"]++;
        $monthly["products"]["$month"]["$block[16]"]["total"]+=$block[19];
        $data[]=$block;
      }
    }
    $rate=$rates["{$request["mc_currency"]}"];
    $mxn=($request["mc_gross"]-$request["mc_fee"])/$rate;
    date_default_timezone_set("America/Mexico_City");
    $data[]=array("", date("d/m/Y", strtotime($request["payment_date"])), date("H:i:s", strtotime($request["payment_date"])), "CDT", $request["first_name"]." ".$request["last_name"],
    "Pagos en sitio web", $request["payment_status"], $request["mc_currency"], $request["mc_gross"], $request["mc_fee"], round($request["mc_gross"]-$request["mc_fee"], 2),
    $request["payer_email"], $request["receiver_email"], $request["txn_id"], $request["address_street"]." ".$request["address_city"]." ".$request["address_zip"]." ".$request["address_state"]." ".$request["address_country"],
    "Confirmada", $request["item_name"], $site, $rate, $mxn);
    $prod_name=$request["item_name"];
    $month=date("Y/m/d:F, d", strtotime($request["payment_date"]));
    // print_r($transaction_day);
    // echo $request["parent_txn_id"];
    if(isset($request["parent_txn_id"]) and isset($transaction_day["{$request["parent_txn_id"]}"]))
    {
      // echo "found - $month";
      $pts=explode("/", $transaction_day["{$request["parent_txn_id"]}"]);
      $month=date("Y/m/d:F, d", strtotime("$pts[2]/$pts[1]/$pts[0] 00:00:01"));
      // echo "new - $month";

    }
    if(!isset($monthly["overall"]["$month"]))
      $monthly["overall"]["$month"]=0;
    if(!isset($monthly["products"]["$month"]["$prod_name"]))
    {
      $monthly["products"]["$month"]["$prod_name"]["qty"]=0;
      $monthly["products"]["$month"]["$prod_name"]["total"]=0;
    }
    $cur=count($data)-1;
    foreach($data[$cur] as $key=>$val)
    {
      $val=mb_convert_encoding($val, "Windows-1252", "UTF-8");
      $data[$cur][$key]=$val;
    }
    if(!is_numeric($data[$cur][19]))
      $data[$cur][19]=0;
    $monthly["overall"]["$month"]+=$data[$cur][19];
    $monthly["products"]["$month"]["$prod_name"]["qty"]++;
    $monthly["products"]["$month"]["$prod_name"]["total"]+=$data[$cur][19];
    save_overall_stat($monthly);
    $writer = new XLSXWriter();
    $writer->writeSheet($data);
    $writer->writeToFile('paypalinfosample.xlsx');
    fwrite($out, "Save data\n");
  }

}
function save_overall_stat($monthly)
{
  $data=array();
  ksort($monthly["overall"]);
  $writer = new XLSXWriter();
  $product_totals=array();
  foreach($monthly["products"] as $month=>$prod_list)
  {
    $month=explode(":", $month)[1];
    $product_totals["$month"]=0;
    foreach($prod_list as $prod_name=>$values)
    $product_totals["$month"]+=$values["qty"];
  }
  foreach($monthly["overall"] as $month=>$val)
  {
    $month=explode(":", $month)[1];
    $data[]=array($month, $product_totals["$month"], round($val, 2));
  }
  $writer->writeSheet($data, "Overall");
  
  /** temp editing by kuldeep @fiverr for creating a new sheet with different date format **/
  $kullu_data=array();
  foreach($monthly["overall"] as $kkey=>$vval)
  {
    $m=explode(":", $kkey);
    $kullu_data[]=array(date("Ymd",strtotime($m[0])), $product_totals["$m[1]"], round($vval, 2));
  }
  $writer->writeSheet($kullu_data, "Totals"); 

  /* temp editing by kuldeep @fiverr*/
 
  $data=array();
  foreach($monthly["products"] as $month=>$prod_list)
  {
    $month=explode(":", $month)[1];
    foreach($prod_list as $prod_name=>$values)
      $data[]=array($month, $prod_name, $values["qty"], round($values["total"], 2));
  }
  $writer->writeSheet($data, "Products");

   /** temp editing by kuldeep @fiverr for creating a new sheet with different date format **/
  $new_data=array();
  foreach($monthly["products"] as $pmonth=>$mprod_list)
  {
    $m1=explode(":", $pmonth);
    foreach($mprod_list as $mprod_name=>$mvalues)
      $new_data[]=array(date("Ymd",strtotime($m1[0])), $mprod_name, $mvalues["qty"], round($mvalues["total"], 2));
  }
  $writer->writeSheet($new_data, "Products_New");
 
   /* temp editing by kuldeep @fiverr*/
 




  $writer->writeToFile('statistics.xlsx');
}
function get_rates()
{
  $data=json_decode(file_get_contents("https://api.exchangeratesapi.io/latest?base=MXN"), true);
  if(!isset($data["rates"]))
    $rates=json_decode(file_get_contents("rates.json"), true);
  else
  {
    $rates=$data["rates"];
    file_put_contents("rates.json", json_encode($rates));
  }
  foreach($rates as $name=>$rate)
    $rates["$name"]=$rate*1.05;
  return $rates;
}
?>
