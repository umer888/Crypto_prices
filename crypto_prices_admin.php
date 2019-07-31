<?php
	global $wpdb;
	$settings = $wpdb->get_results("SELECT coins_with_links,active_currency,active_language FROM ".$wpdb->prefix."crypto_prices_setting ");

	if(!empty($settings[0]->coins_with_links)){
	    $coins_array = implode(",",json_decode($settings[0]->coins_with_links));
	}

	if (!empty($settings[0]->coins_with_links)) {
		$generator = json_decode($settings[0]->coins_with_links);
	}

	//$generator = json_decode($settings[0]->coins_with_links);
    
    if (empty($generator)) {
    	$generator = array();
    }

?>

<style>
	.wrap{
		padding: 20px;
	}
	input,select{
		width:200px;
		height: 40px !important;
	
	}
	form{
		margin-top: 8%;
	}
	.coinlinks{
    margin-left: 17%;
	}  
	textarea{
		margin-left: 7%;
	}
	.button{
		width: 40px !important;
	}
	.submit{
		margin-left: 18%;
	}
	
</style>

<div id="notice" style="width:100%;">
	
</div>


<div class="wrap">
    <?php    echo "<h2>" . __( 'Crypto Prices Setting Options', 'cypto_trdom' ) . "</h2>"; ?>
     
    <form name="cypto_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        
		<div id='TextBoxesGroup'>
			<div id="TextBoxDiv1">
				<label>Coin #1 : </label><input type='textbox' name="linked_coins[]" class="coinlinks" id='textbox1' >
			</div>
		</div>
        
        <p><?php _e("Active Market Currency: " ); ?><select name="currency" style="margin-left:129px !important;">
		                                            <option value="">Select Currency</option>
													<option value="usd" <?php if(!empty($settings[0]->active_currency) && $settings[0]->active_currency == "usd"){ echo "selected"; }?>>USD</option>
													<option value="nok" <?php if(!empty($settings[0]->active_currency) && $settings[0]->active_currency == "nok"){ echo "selected"; }?>>NOK</option>
													<option value="dkk" <?php if(!empty($settings[0]->active_currency) && $settings[0]->active_currency == "dkk"){ echo "selected"; }?>>DKK</option>
													<option value="sek" <?php if(!empty($settings[0]->active_currency) && $settings[0]->active_currency == "sek"){ echo "selected"; }?>>SEK</option>
													</select>
		</p>
        <p><?php _e("Active Language: " ); ?><select name="language" style="margin-left: 170px!important;"> 
		                                            <option value="">Select Language</option>
													<option value="english" <?php if(!empty($settings[0]->active_language) && $settings[0]->active_language == "english"){ echo "selected"; }?>>English</option>
													<option value="norweign" <?php if(!empty($settings[0]->active_language) && $settings[0]->active_language == "norweign"){ echo "selected"; }?>>Norwegian</option>
													<option value="swedish" <?php if(!empty($settings[0]->active_language) && $settings[0]->active_language == "swedish"){ echo "selected"; }?>>Swedish</option>
													<option value="danish" <?php if(!empty($settings[0]->active_language) && $settings[0]->active_language == "danish"){ echo "selected"; }?>>Danish</option>
											</select>
		</p>
        

												     
												        <p class="submit">
														<input type='button' class="button" value='+' id='addButton'>
														<input type="button" id="submit" name="Submit" value="<?php _e('Update Options', 'oscimp_trdom' ) ?>" />
														<input type='button' class="button" value='_' id='removeButton'>
												        </p>
    </form>
</div>

<script type="text/javascript">

jQuery(document).ready(function(){

//main data submission

			jQuery("#submit").click(function () {

                      var coin_names = [];
                      var i = 0;
                      jQuery('input[name^="linked_coins"]').each(function() {
						   coin_names.push(jQuery(this).val());
						});
                      var languagesss  =  jQuery("select[name='language']").val();
                      var currenciesss =  jQuery("select[name='currency']").val();

						jQuery.ajax({
						   	  type: "POST",
							  url: ajaxurl,
							  cache: false,
							  data : {action: 'myaction', linked_coins : coin_names, currency : currenciesss, language : languagesss, Submit: "yes" },
							  success: function(html){
							      jQuery("#notice").append(html);
							  }
						});
				});


		    var coins_array = [];
		    var counter = 2;
		    <?php for($i = 0; $i < count($generator); $i++){?>
		           coins_array[<?php echo $i;?>] = "<?php echo $generator[$i];?>";
		     <?php } ?>
    

			if(coins_array.length > 0){
					if(coins_array.length == 1){
				         jQuery('#textbox1').val(coins_array[0]);
					   }
					   if(coins_array.length > 1){
					       jQuery('#textbox1').val(coins_array[0]);
							for(j=1; j<coins_array.length; j++){
								var newTextBoxDiv = jQuery(document.createElement('div')).attr("id", 'TextBoxDiv' + counter);
									newTextBoxDiv.after().html('<label>Coin #'+ counter + ' : </label>' +
									'<input type="text" class="coinlinks" name="linked_coins[]" id="textbox' + counter + '" value="'+coins_array[j]+'" >');
									newTextBoxDiv.appendTo("#TextBoxesGroup");		
									counter++;
							}
				     }
			  }



    jQuery("#addButton").click(function () {
			if(counter>10){
					alert("Only 10 textboxes allow");
					return false;
			}   
			var newTextBoxDiv = jQuery(document.createElement('div')).attr("id", 'TextBoxDiv' + counter);
			newTextBoxDiv.after().html('<label>Coin #'+ counter + ' : </label>' +
			'<input type="text" class="coinlinks" name="linked_coins[]" id="textbox' + counter + '" value="" >');
			newTextBoxDiv.appendTo("#TextBoxesGroup");		
			counter++;
     });

     jQuery("#removeButton").click(function () {
			if(counter==2){
		          alert("You at least have to keep one box");
		          return false;
		       }   
			counter--;
		    jQuery("#TextBoxDiv" + counter).remove();	
     });
  });
</script>