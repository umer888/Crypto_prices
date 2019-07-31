<?php
/**
 * Plugin Name: Crypto prices
 * Plugin URI:  http://xlogicsolutions.com/
 * Description: The plugin for the prices details for top 100 crypto currencies
 * Version:     1.0.0
 * Author:      Umer the Dev
 */


/*
*  Adding styles and scripts
*/

function script_installer(){
    
	wp_enqueue_style( 'plugin-styles', plugins_url().'/crypto_prices/css/crypto_style.css');
	wp_enqueue_style( 'chart-styles', plugins_url().'/crypto_prices/css/chart-styles.css');
	wp_enqueue_script( 'plugin-scripts', plugins_url().'/crypto_prices/js/Chart.min.js', array( 'jquery' ), '1.12.4', true);
	wp_enqueue_script( 'numeral', "https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js", array( 'jquery' ), '1.12.4', true);
	wp_enqueue_script( 'chart-scripts', plugins_url().'/crypto_prices/js/crypto_scripts.js', array( 'jquery' ), '1.12.4', true);
    wp_localize_script('tags-getting-script', 'tagscript', array(
        'pluginsUrl' => plugins_url(),
    ));
	//wp_enqueue_script( 'graph-script', 'https://canvasjs.com/assets/script/jquery.canvasjs.min.js');
	
  }
  add_action( 'wp_enqueue_scripts', 'script_installer' );



/*
*  Add settings menu item to add for the form
*/

if( !class_exists( 'WP_Http' ) )
	include_once( ABSPATH . WPINC. '/class-http.php' );
	

function add_menu(){
	add_options_page("Crypto prices settings", "Crypto Prices Setting Panel",  "activate_plugins","settings_panel","dashboard_setting_panel");
} 
    add_action('admin_menu', 'add_menu');

/*
*  Form to add new cattery
*/

function dashboard_setting_panel(){
	 include('crypto_prices_admin.php');
}

/*
*  Adding new catteries table when the plugin is activated
*/


function table_for_setting() {

    global $wpdb;
    $table_name = $wpdb->prefix . 'crypto_prices_setting';
    $pf_parts_db_version = '1.0.0';
    $charset_collate = $wpdb->get_charset_collate();

    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {

        $sql = "CREATE TABLE $table_name (
                        id int(9) NOT NULL AUTO_INCREMENT,
						coins_with_links    varchar(100) NOT NULL,
						active_currency     varchar(30) NOT NULL DEFAULT 'usd',
						active_language     varchar(30) NOT NULL DEFAULT 'english',
						headings_english    varchar(200) NOT NULL,
						headings_norweign   varchar(200) NOT NULL,
						headings_swedish    varchar(200) NOT NULL,
						headings_danish     varchar(200) NOT NULL,
						graph_heading_english    varchar(200) NOT NULL,
						graph_heading_norweign   varchar(200) NOT NULL,
						graph_heading_swedish    varchar(200) NOT NULL,
						graph_heading_danish     varchar(200) NOT NULL,
                        PRIMARY KEY  (id)
                        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        add_option( 'pf_parts_db_version', $pf_parts_db_version );
    }
}
register_activation_hook( __FILE__, 'table_for_setting' );


/*
 *  Shortcode generating function for top 100 coins
 */


 function top_currencies_table(){


	global $wpdb;    
	$check_data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."crypto_prices_setting");

	if(empty($check_data)){
	
		$headings_english = array("Coin","Price","24h Change","Market cap","24h volume","Supply","Price Chart (7D)");
		$headings_norweign = array("Coin","Pris","Endring 24t","Markedsverdi","Volum 24t","Supply","Pris (7 dager)");
		$headings_swedish = array("Kryptovaluta","Pris","Förändring 24h","Borsvarde","Volym 24h","Utbud","Prisdiagram");
		$headings_danish = array("Kryptovaluta","Prisoversigt","Andring 24t","Markedsværdi","24h bind","Levere","Prisoversigt (7D)");

		$graph_headings_english = array("Price","1 Hour","1 Day","1 Week","Market cap","24h volume");
		$graph_headings_norweign = array("Pris","1 time","1 dag","1 uke","Markedsverdi","Volum 24t");
		$graph_headings_swedish = array("Pris","1 Timme","1 Dag","1 vecka","Borsvarde","Volym 24h");
		$graph_headings_danish = array("Pris","1 time","1 dag","1 uge","MarkedKasket","24h bind");

		
		$wpdb->insert($wpdb->prefix.'crypto_prices_setting', array(
			'active_currency' => 'usd',
			'active_language' => 'english',
			'headings_english' => json_encode($headings_english),
			'headings_norweign' => json_encode($headings_norweign),
			'headings_swedish' => json_encode($headings_swedish),
			'headings_danish' => json_encode($headings_danish),
			'graph_heading_english' => json_encode($graph_headings_english),
			'graph_heading_norweign' => json_encode($headings_norweign),
			'graph_heading_swedish' => json_encode($headings_swedish),
			'graph_heading_danish' => json_encode($headings_danish),
		));
}

	$settings_data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."crypto_prices_setting");
	

	if(!empty($settings_data)){
		$linked_coins = json_decode($settings_data[0]->coins_with_links);
	}
	$active_currency = $settings_data[0]->active_currency;


	if($active_currency == "usd"){
		$currency_symbol = '$';
	}else if($active_currency == "nok"){
        $currency_symbol = 'NOK';
	}else if($active_currency == "dkk"){
        $currency_symbol = 'DKK';
	}else if($active_currency == "sek"){
        $currency_symbol = 'SEK';
	}


	 $active_language = $settings_data[0]->active_language;
	if($active_language == "english"){
		$headings = $settings_data[0]->headings_english;
	}else if($active_language == "norweign"){
		$headings = $settings_data[0]->headings_norweign;
	}else if($active_language == "swedish"){
		$headings = $settings_data[0]->headings_swedish;
	}else if($active_language == "danish"){
		$headings = $settings_data[0]->headings_danish;
	}

	
	
	

	$headings = json_decode($headings);
	//$headings = explode(",",$headings);

	

	//getting data of the 100 coins by API

	$url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency='.$active_currency.'&order=market_cap_desc&per_page=100&page=1&sparkline=false';
    $request = new WP_Http;
    $result = $request->request( $url );
	$json = $result['body'];
	$coins = json_decode($json, true);
	$chart_array= array(1,279,44,2,780,738,825,6799,325,975,1094,100,8418,69,19,1481,877,480,692,2822,973,3447,453,242,486,4463,7310,1364,1043,684,1167,5,677,6319,4703,776,7492,329,3348,7595,1087,385,1085,3449,3412,1254,316,2170,425,611,863,92,756,95,309,5795,6013,99,2523,1060,2687,1091,63,4755,1086,1048,1089,289,203,2538,525,398,1152,1102,1371,80,779,739,3370,542,479,1442,531,3139,878,3246,3849,1372,2431,4380,4643,1053,1768,691,7340,1107,5003,613,2780,3695,6425);

	//getting data for update at
	$urll = 'https://api.coingecko.com/api/v3/global';
	$resuestt = new WP_Http;
	$resultt = $resuestt->request($urll);
	$jsonn = $resultt['body'];
	$dat = json_decode($jsonn, true);
	$epoch = $dat['data']['updated_at'];
	$dt = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
	$time = strtotime($dt->format('Y-m-d H:i:s'));

	///////////////////////////////////

	function humanTiming ($time)
	{

    	$time = time() - $time; // to get the time since that moment
    	$time = ($time<1)? 1 : $time;
    	$tokens = array (
        	31536000 => 'year',
        	2592000 => 'month',
        	604800 => 'week',
        	86400 => 'day',
        	3600 => 'hour',
        	60 => 'minute',
        	1 => 'second'
    	);

    	foreach ($tokens as $unit => $text) {
        	if ($time < $unit) continue;
        	$numberOfUnits = floor($time / $unit);
        	return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    	}
	}

	///////////////////////////////////


	$get_active_market_currency = $wpdb->get_results("SELECT active_currency,active_language FROM ".$wpdb->prefix."crypto_prices_setting");
	if ($get_active_market_currency[0]->active_currency == 'usd') {
		$symbol = '$';
    	$mktcap   = $dat['data']['total_market_cap']['usd'];
    	$tlvolume = $dat['data']['total_volume']['usd'];
    	$dominance = $dat['data']['market_cap_percentage']['btc'];
    }elseif($get_active_market_currency[0]->active_currency == 'eur'){
    	$symbol = '€';
    	$mktcap = $dat['data']['total_market_cap']['eur'];
    	$tlvolume = $dat['data']['total_volume']['eur'];
    	$dominance = $dat['data']['market_cap_percentage']['btc'];
    }elseif($get_active_market_currency[0]->active_currency == 'nok'){
    	$symbol = 'NOK';
    	$mktcap = $dat['data']['total_market_cap']['nok'];
    	$tlvolume = $dat['data']['total_volume']['nok'];
    	$dominance = $dat['data']['market_cap_percentage']['btc'];
    }elseif($get_active_market_currency[0]->active_currency == 'dkk'){
    	$symbol = 'DKK';
    	$mktcap = $dat['data']['total_market_cap']['dkk'];
    	$tlvolume = $dat['data']['total_volume']['dkk'];
    	$dominance = $dat['data']['market_cap_percentage']['btc'];
    }elseif($get_active_market_currency[0]->active_currency == 'sek'){
    	$symbol = 'SEK';
    	$mktcap = $dat['data']['total_market_cap']['sek'];
    	$tlvolume = $dat['data']['total_volume']['sek'];
    	$dominance = $dat['data']['market_cap_percentage']['btc'];
    }



    if ($get_active_market_currency[0]->active_language == 'english') {
    	$m = 'Market Cap';
    	$v = '24h volume';
    	$d = 'BTC dominance';
    	$top = 'Top 100 Cryptocurrency Prices';
    }elseif ($get_active_market_currency[0]->active_language == 'norweign') {
    	$m = 'Markedsverdi';
    	$v = 'Volum 24t';
    	$d = 'BTC-dominanse';
    	$top = 'Prisoversikt topp 100 kryptovaluta';
    }elseif ($get_active_market_currency[0]->active_language == 'swedish') {
    	$m = 'Börsvärde';
    	$v = 'Volym 24h';
    	$d = 'BTC-dominans';
    	$top = 'Kurslista topp 100 kryptovalutor';
    }elseif ($get_active_market_currency[0]->active_language == 'danish') {
    	$m = 'Markedsværdi';
    	$v = 'Volumen 24t';
    	$d = 'BTC-dominans';
    	$top = 'Prisoversigt: Top 100 kryptovaluta';
    }
    
      if($currency_symbol == "$"){

                      
                        $mktcap = $symbol.nice_number($mktcap);
                        $tlvolume = $symbol.nice_number($tlvolume);

                      }else{

                        $mktcap = nice_number($mktcap)." ".$symbol;
                        $tlvolume = nice_number($tlvolume)." ".$symbol;
                      }


    $output = '';
   //building output
    $output .= "<div class='full-width-crypto list-hundred-container' style='padding-top: 40px;'>";

    $output .= "<div class='full-width-crypto upper-crypto-calculations' style='margin: 0 auto; max-width: 90% !important;'>";


    $output .= 	'<div style="display: inline;"><div style="float:left"><h4 class="top-headings">'.$top.'</h4>
    				<p class="small-headings">Updated '.humanTiming($time).' ago</p></div>
    				<div class="values">
    					<div style="float: left; width: 33%;">
    						<h4 class="top-headings" style="text-align: center; font-weight: normal;">'.$mktcap.'</h4>
    						<h6 class="small-headings" style="text-align: center;">'.$m.'</h6>
    					</div>
    					<div style="float: left; width: 33%;">
    						<h4 class="top-headings" style="text-align: center; font-weight: normal;">'.$tlvolume.'</h4>
    						<h6 class="small-headings" style="text-align: center;">'.$v.'</h6>
    					</div>
    					<div style="float: left; width: 33%;">
    						<h4 class="top-headings" style="text-align: center; font-weight: normal;">'.nice_number($dominance).'%</h4>
    						<h6 class="small-headings" style="text-align: center;">'.$d.'</h6>
    					</div>
    				</div>
    			</div>';


    $output .= "</div>";

	   $output .= "<table class='list-hundred'>";
	   $output .= "<thead> <tr><td></td>";
  
	for($i=0; $i< count($headings); $i++){

		if($i >= 1 && $i < 5){ $class = "class='text-right'"; }elseif($i == 5){ $class = "class='text-center'"; }else{ $class = '';}
		
		
		$output .= "<td ".$class.">".$headings[$i]."</td>";
	}
	   $output .="</tr> </thead>";
	   $output .= "<tbody>";
	 
         foreach($coins as $key => $coin){
			 
			$key = $key + 1;
			if($coin['price_change_percentage_24h'] < 0){
                  $color = "#dc3545";
			}else{
				  $color = "#28a745";
			}
			$output .= "<tr>";
			$output .= "<td width='5%' class='text-muted small pl-4 text-center' width='10%'>".$key."</td>";
			$output .= "<td width='25%'>";
        if(!empty($linked_coins) && in_array($coin['name'],$linked_coins)){
			$output .= "<a href='".get_site_url()."/price/".str_replace(" ","-",strtolower($coin['name']))."' class='d-flex no-underline'>";
			$class = "btn-link";
		}else{ 
			$output .= "<a class='d-flex no-underline'>";
			$class = "btn-without-link";
		}
			$output .= "<div class='my-auto mr-4'>
			            <image src='".$coin['image']."'style='width: 32px; height: 32px; margin:auto;' alt='Bitcoin Price'/>
		            	</div>
		            	<div class='my-auto ml-2'>
			            <h2 class='h0 underline-on-hover ".$class."'>".$coin['symbol']."</h2>
		             	<div class='small text-muted no-underline' style='margin-bottom: 9px;'>".$coin['name']."</div>
						</div>";

				      if($currency_symbol == "$"){

                        $price = $currency_symbol.number_format( $coin['current_price'], 2 );
                        $market_cap = $currency_symbol.nice_number( $coin['market_cap'], 2 );
                        $totalvol = $currency_symbol.nice_number( $coin['total_volume'], 2 );

                      }else{

                        $price = number_format( $coin['current_price'], 2 ).'<span > '.$currency_symbol.' </span>';
                        $market_cap = nice_number( $coin['market_cap'], 2 ).'<span > '.$currency_symbol.' </span>';
                        $totalvol = nice_number( $coin['total_volume'], 2 ).'<span > '.$currency_symbol.' </span>';
                      }
				
			$output .=	"</a>";
	    
			$output .=	"</td>";
			
			$output .= "<td class='text-right' width='10%'>".$price."</td>";

			$output .= "<td class='text-right' width='10%' style='color:".$color."'>".round($coin['price_change_percentage_24h'],2)."%</td>";

			$output .= "<td class='text-right' width='10%'>".$market_cap."</td>";

			$output .= "<td class='text-right' width='10%'>".$totalvol."</td>";

			$output .= "<td class='text-center' width='15%'>".nice_number($coin['circulating_supply'])."</td>";

			$output .= "<td  width='15%'><image src='https://www.coingecko.com/coins/".$chart_array[$key]."/sparkline' style='width:100%; height: 100%;'/></td>";
			$output .= "</tr>";
			
		 }
		 $output .= "</tbody>";
		 $output .= "</table>";

    $output .= "</div>";
   
    return $output;
 }

 add_shortcode('crypto_prices_table', 'top_currencies_table');

/*
 *  Shortcode generating function for price history graph for a single coin
 */

 function generate_graph($coin){

	
	global $wpdb;    
	$coin = strtolower($coin[0]);
	$settings_data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."crypto_prices_setting");
	$active_currency = $settings_data[0]->active_currency;
	$active_language = $settings_data[0]->active_language;

	if($active_language == 'english'){
	   $graph_headings = $settings_data[0]->graph_heading_english;
	   $graph_headings = json_decode($graph_headings);
	}else if($active_language == 'norweign'){
	   $graph_headings = $settings_data[0]->graph_heading_norweign;
	   $graph_headings = json_decode($graph_headings);

	}else if($active_language == 'swedish'){
	   $graph_headings = $settings_data[0]->graph_heading_swedish;
	   $graph_headings = json_decode($graph_headings);

	}else if($active_language == 'danish'){
	   $graph_headings = $settings_data[0]->graph_heading_danish;
	   $graph_headings = json_decode($graph_headings);

	}



	if($active_currency == "usd"){
		$currency_symbol = '$';
	}else if($active_currency == "nok"){
        $currency_symbol = 'NOK';
	}else if($active_currency == "dkk"){
        $currency_symbol = 'DKK';
	}else if($active_currency == "sek"){
        $currency_symbol = 'SEK';
	}
	include('new_graph.php');
	
 }


add_shortcode('generate-prices-graph', 'generate_graph');


/*
 *  number formatter function
 */


 function nice_number($n) {
	// first strip any formatting;
	$n = (0+str_replace(",", "", $n));

	// is this a number?
	if (!is_numeric($n)) return false;

	// now filter it;
	if ($n > 1000000000000) return round(($n/1000000000000), 1).'T';
	elseif ($n > 1000000000) return round(($n/1000000000), 1).'B';
	elseif ($n > 1000000) return round(($n/1000000), 1).'M';
	elseif ($n > 1000) return round(($n/1000), 1).'K';

	return number_format($n);
}



add_action( "wp_ajax_myaction", "so_wp_ajax_function" );
add_action( "wp_ajax_nopriv_myaction", "so_wp_ajax_function" );

function so_wp_ajax_function(){
  //DO whatever you want with data posted
  //To send back a response you have to echo the result!
	global $wpdb;
	
	$linked_coins   = $_POST['linked_coins'];
	$linked_coins   = json_encode($linked_coins);
	$currency       = $_POST['currency'];
	$language       = $_POST['language'];
	
	$sql = "SELECT coins_with_links,active_currency,active_language FROM ".$wpdb->prefix."crypto_prices_setting ";
	$settings       = $wpdb->get_results($sql);


	if(!empty($settings)){

		 $updated = $wpdb->update($wpdb->prefix.'crypto_prices_setting', array('coins_with_links'=> $linked_coins, 'active_currency'=> $currency, 'active_language'=> $language), array('Id' => '1'));
		if($updated){
			
					echo '<div class="updated notice">
					<p>Settings has been updated successfully, Thanks</p>
					</div>';
		}else{
			
					echo '<div class="error notice">
					<p>Please change any value.</p>
				</div>';
		}
        
	}else{

		 $result = $wpdb->query( "INSERT INTO `".$wpdb->prefix."crypto_prices_setting` (coins_with_links, active_currency, active_language) VALUES ('$linked_coins', '$currency', '$language')");
		 if($result){
		 	
					echo '<div class="updated notice">
					<p>Settings has been updated successfully, Thanks</p>
					</div>';

		 }else{
		 
					echo '<div class="error notice">
						<p>There has been an error. Please try again later</p>
					</div>';
	        }

    	}

  wp_die(); // ajax call must die to avoid trailing 0 in your response
}