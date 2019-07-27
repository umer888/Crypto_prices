<?php
$url = 'https://api.coingecko.com/api/v3/coins/'.$coin;
$request = new WP_Http;
$result = $request->request( $url );
$json = $result['body'];
$coin_status = json_decode($json, true);
$current_price = $currency_symbol.$coin_status['market_data']['current_price'][$active_currency];
$one_hour_change = $coin_status['market_data']['price_change_percentage_1h_in_currency'][$active_currency];
$one_day_change = $coin_status['market_data']['price_change_percentage_24h_in_currency'][$active_currency];
$one_week_change = $coin_status['market_data']['price_change_percentage_7d_in_currency'][$active_currency];
$market_cap = $coin_status['market_data']['market_cap'][$active_currency];
$volume = $coin_status['market_data']['total_volume'][$active_currency];
?>
<div class="full-width-crypto">
  <div class="cryto-coin-title">
    
    <div class="d-flex flex-wrap align-items-baseline taitle">
      <div class="crypto-thumb my-auto mr-3">
        <img src="<?php echo $coin_status['image']['small']; ?>" width="80%" style="margin-top: 34%;">
      </div>
        <h2><?php echo $coin_status['name']; ?> Price</h2>
        <div class="h4 text-muted" style="text-transform: uppercase; margin-left: 1%;">   ( <?php echo $coin_status['symbol']; ?> )</div>
    </div>
    
  </div>

    <div class="graph-outer">
        <div class="range-outer upper-stats">
            <div class="crypto-block">
                 <div class="text-muted small" style="/* height: 25px; */"><?php echo $graph_headings[0]; ?></div>
                 <div>
                    <div class="my-auto h4 ">
                        <span><?php echo $current_price; ?></span>
                    </div>
                  </div>
            </div>
            <div class="crypto-block" style="padding-left">
                 <div class="text-muted small"><?php echo $graph_headings[1]; ?></div>
                 <div>
                    <div class="my-auto h4 " style="<?php if($one_hour_change > 0){ echo "color:#28a745"; }else{ echo "color:#dc3545"; } ?>">
                        <span><?php echo round($one_hour_change,2)."%";?></span>
                    </div>
                  </div>
            </div>
            <div class="crypto-block">
                 <div class="text-muted small"><?php echo $graph_headings[2]; ?></div>
                 <div>
                    <div class="my-auto h4 " style="<?php if($one_day_change > 0){ echo "color:#28a745"; }else{ echo "color:#dc3545"; } ?>">
                        <span><?php echo round($one_day_change,2)."%";?></span>
                    </div>
                  </div>
            </div>
            <div class="crypto-block">
                 <div class="text-muted small" style="/* height: 25px; */"><?php echo $graph_headings[3]; ?></div>
                 <div>
                    <div class="my-auto h4 "  style="<?php if($one_week_change > 0){ echo "color:#28a745"; }else{ echo "color:#dc3545"; } ?>">
                        <span><?php echo round($one_week_change,2)."%";?></span>
                    </div>
                  </div>
            </div>
            <div class="crypto-block">
                 <div class="text-muted small" style="/* height: 25px; */"><?php echo $graph_headings[4]; ?></div>
                 <div>
                    <div class="my-auto h4 ">
                        <span><?php echo $currency_symbol.nice_number($market_cap);?></span>
                    </div>
                  </div>
            </div>
            <div class="crypto-block">
                 <div class="text-muted small" style="/* height: 25px; */"><?php echo $graph_headings[5]; ?></div>
                 <div>
                    <div class="my-auto h4 ">
                        <span><?php echo $currency_symbol.nice_number($volume);?></span>
                    </div>
                  </div>
            </div>
        </div>
        <div class=" range-outer text-muted small" style="height:40px">
            <ul class="range-buttons">
                <li id="week">1W</li>
                <li id="month">1M</li>
                <li id="year">1Y</li>
                <li id="all" class="active">All</li>
            <ul>

        </div>
        <canvas  id="chartContainer">
        </canvas>
    </div>
</div>
<script>
jQuery(document).ready(function() {

        jQuery('.range-buttons li').click(function(){
        jQuery('.range-buttons li').removeClass('active');
        jQuery(this).addClass('active');
    });


var generate_graph = function(till, from, year=false,  all=false) {
        
    var coin = "<?php echo $coin;?>";
    var active_currency = "<?php echo $active_currency;?>";
    var today = new Date();
    var current_date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
    
    if(all){
        var date = new Date("01/05/2013 16:00:00"); // some mock date
        start_date = Math.floor(date.getTime()/1000.0);

    }else{
        var start_date = get_epoch_with_difference(current_date,from);
    }

    var end_date = get_epoch_with_difference(current_date,till);
    var res = get_api_records(coin, active_currency, start_date, end_date, year,  all);

 
    var titles = [];
    var values = [];
      res.forEach(function(one){
        
        titles.push(one['label']);
        values.push(one['y']);3
    
      });

    var ctx = document.getElementById('chartContainer').getContext('2d');
    var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
        labels: titles,
        datasets: [{
            label: 'Price',
            backgroundColor: '#cce6ff',
            borderColor: '#0083ff',
            data: values,
            pointRadius: 0,

        }]
    },

    // Configuration options go here
    options: {
      tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var label = data.datasets[tooltipItem.datasetIndex].label || '';

                    if (label) {
                        label += ': ';
                    }
                    label += Math.round(tooltipItem.yLabel * 100) / 100;
                    return label;
                }
            }
        },
      legend: {
            display: false
        }
      }
});

}

//default graph
/*window.onload = function () {
   generate_graph(1,0,false,true);
}*/
//one week
jQuery("#all").click(function(){
    generate_graph(1,0,false,true);
}); 

//one week
jQuery("#week").click(function(){
    generate_graph(1,7);
}); 
//one month
jQuery("#month").click(function(){
    generate_graph(1,30);
}); 
//one year
jQuery("#year").click(function(){
    generate_graph(1,360,true);
}); 


//The previous dates in epoch format
var get_epoch_with_difference = function(date, duration) {
        var dt = new Date(date);
        var previous_date = new Date((dt.setDate(dt.getDate()-duration))).toString();
        var myDate = new Date(previous_date); // Your timezone!
        var myEpoch = myDate.getTime()/1000.0;
        return myEpoch;
 }


 var get_api_records = function(coin,market,start,end,year=false,all=false) {
    
    alert("yes");
        var date_checker;
        var final_array = [];
        var counter = 0;
        jQuery.ajax({
            async: false,
            url: "https://api.coingecko.com/api/v3/coins/"+coin+"/market_chart/range?vs_currency="+market+"&from="+start+"&to="+end+"",
            cache: false,
           success: function(response){
                
               var prices =  response.prices;

               for(var i = 0; i< prices.length; i++){

                if(year || all){
                    var date = new Date(prices[i][0]).toLocaleString();
                    date = date.substring(0, 10);
                }else{
                    var date = new Date(prices[i][0]).toGMTString();
                    date = date.substring(5, 12);
                }
                    if(date_checker != date){
                        var saving_date = date.replace(/\s/g, '');
                        final_array[counter] = {label: saving_date, y: prices[i][1]};
                        date_checker = date;
                        counter++;
                    }
                }
            }
        });
        return final_array;
  }

});

</script>