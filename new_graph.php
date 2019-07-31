<?php
$url = 'https://api.coingecko.com/api/v3/coins/'.$coin;
$request = new WP_Http;
$result = $request->request( $url );
$json = $result['body'];
$coin_status = json_decode($json, true);
$current_price = $coin_status['market_data']['current_price'][$active_currency];
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
                      <?php if($currency_symbol == "$"){
                        echo "<span>".$currency_symbol.$current_price."</span>";
                      }else{
                        echo '<span>'.$current_price.'</span><span class="currencysymbol"> '.$currency_symbol.' </span>';;
                      }?>
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
                      <?php if($currency_symbol == "$"){
                        echo "<span>".nice_number($market_cap).$currency_symbol."</span>";
                      }else{
                        echo '<span>'.nice_number($market_cap).'</span><span class="currencysymbol"> '.$currency_symbol.' </span>';;
                      }?>
                    </div>
                  </div>
            </div>
            <div class="crypto-block">
                 <div class="text-muted small" style="/* height: 25px; */"><?php echo $graph_headings[5]; ?></div>
                 <div>
                    <div class="my-auto h4 ">
                      <?php if($currency_symbol == "$"){
                        echo "<span>".nice_number($volume).$currency_symbol."</span>";
                      }else{
                        echo '<span>'.nice_number($volume).'</span><span class="currencysymbol"> '.$currency_symbol.' </span>';;
                      }?>
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

var chart;

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
        values.push(one['y']);
      });







      /*----For drawing line on the chart----*/
    Chart.defaults.LineWithLine = Chart.defaults.line;
    Chart.controllers.LineWithLine = Chart.controllers.line.extend({
    draw: function(ease) {
    Chart.controllers.line.prototype.draw.call(this, ease);

      if (this.chart.tooltip._active && this.chart.tooltip._active.length) {
         var activePoint = this.chart.tooltip._active[0],
             ctx = this.chart.ctx,
             x = activePoint.tooltipPosition().x,
             topY = this.chart.scales['y-axis-0'].top,
             bottomY = this.chart.scales['y-axis-0'].bottom;

         // draw line
         ctx.save();
         ctx.beginPath();
         ctx.moveTo(x, topY);
         ctx.lineTo(x, bottomY);
         ctx.lineWidth = 1;
         ctx.strokeStyle = '#07C';
         ctx.stroke();
         ctx.restore();
      }
   }
});
/*--------*/


    var ctx = document.getElementById('chartContainer').getContext('2d');

    chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'LineWithLine',

    // The data for our dataset
    data: {
        labels: titles,
        datasets: [{
            label: 'Price',
            backgroundColor: '#cce6ff',
            borderColor: '#0083ff',
            
            data: values,
            pointRadius: 0,
            'borderWidth': '2',
            'lineTension': '0.2'
        }],
    },

    // Configuration options go here
    options: {
      tooltips: {
            intersect: false,
            mode: 'index',
            displayColors: true,

            callbacks: {
                label: function(tooltipItem, data) {
                    var label = data.datasets[tooltipItem.datasetIndex].label || '';

                    if (label) {
                        label += ': ';
                    }
                    label += Math.round(tooltipItem.yLabel * 100) / 100;
                    return label;
                },

                title: function(tooltipItem){
                // `tooltipItem` is an object containing properties such as
                // the dataset and the index of the current item

                // Here, `this` is the char instance

                // The following returns the full string

                if (mnth) {
                  var pieces = this._data.labels[tooltipItem[0].index].split(" ");
                  switch(pieces[2]) {
                    case 'Jan':
                      pieces[2] = '01';
                      break;
                    case 'Feb':
                      pieces[2] = '02';
                      break;
                    case 'Mar':
                      pieces[2] = '03';
                      break;
                    case 'Apr':
                      pieces[2] = '04';
                      break;
                    case 'May':
                      pieces[2] = '05';
                      break;
                    case 'Jun':
                      pieces[2] = '06';
                      break;
                    case 'Jul':
                      pieces[2] = '07';
                      break;
                    case 'Aug':
                      pieces[2] = '08';
                      break;
                    case 'Sep':
                      pieces[2] = '09';
                      break;
                    case 'Oct':
                      pieces[2] = '10';
                      break;
                    case 'Nov':
                      pieces[2] = '11';
                      break;
                    case 'Dec':
                      pieces[2] = '12';
                      break;
                    default:
                      // code block
                  }
                  return pieces[2]+"/"+pieces[1]+"/"+pieces[3];
                }
                if(checker){
                  return this._data.labels[tooltipItem[0].index];
                }
                
                console.log(this._data.labels[tooltipItem[0].index]);
                }
            }
        },
        scales: {


          xAxes: [{
                gridLines: {
                    display: false
                },
                ticks: {
                    fontColor: '#9E9E9E',
                    maxRotation: 0,
                    autoSkipPadding: 110,
                    //autoSkip: true,
                    //maxTicksLimit:4
                    callback: function(tick) {
                      if(chkweek){
                        tick = '12AM';
                        return tick;
                      }
                      if(checker){
                        var characterLimit = 4;
                        if (tick.length >= characterLimit) {
                            tick = tick.substring(tick.length-5);
                            tick = tick.replace(/\//g, '');
                            return tick;
                        }
                      }
                      if(mnth){
                        var characterLimit = 6;
                        if (tick.length >= characterLimit) {
                            tick = tick.substring(5,11);
                            var pieces = tick.split(" ");
                            tick = pieces[1]+" "+pieces[0];
                            return tick;
                        }
                      }
                      
                    return tick;
                  }
                }
                //type: 'time'
               

            }],

          yAxes: [{
            
            ticks: {
                    fontColor: '#9E9E9E',
                    callback: function(value, index, values) {
                        return formatCurrencyAbbreviated(value);
                    },
                    padding: 5,
            },
            scaleLabel: {
                display: false,
                labelString: 'probability'
            }
          }]
        },
      legend: {
            display: false
        }
      
      }
});

}





function formatCurrencyAbbreviated(number) {
    if (number < 1) {
        return addCurrencySymbol(number.toPrecision(3));
    } else {
        return addCurrencySymbol(numeral(number).format('0.0a'));
    }
}

function addCurrencySymbol(value) {
    
        return '' + value;
    
}



//default graph
window.onload = function () {
    chkweek = false;
    mnth = false;
   generate_graph(1,0,false,true);
}
//one week
jQuery("#week").click(function(){
    chart.destroy();
    chkweek = true;
    mnth = false;
    checker = false;
    generate_graph(1,7);
}); 
//one month
jQuery("#month").click(function(){
    chart.destroy();
    mnth = true;
    chkweek = false;
    checker = false;
    generate_graph(1,30);
}); 
//one year
jQuery("#year").click(function(){
    chart.destroy();
    checker = true;
    mnth = false;
    chkweek = false;
    generate_graph(1,360,true);
}); 

jQuery("#all").click(function(){
    chart.destroy();
    chkweek = false;
    mnth = false;
    generate_graph(1,0,false,true);
}); 


//The previous dates in epoch format
var get_epoch_with_difference = function(date, duration) {
        var dt = new Date(date);
        var previous_date = new Date((dt.setDate(dt.getDate()-duration))).toString();
        var myDate = new Date(previous_date); // Your timezone!
        var myEpoch = myDate.getTime()/1000.0;
        return myEpoch;
 }

var checker = false;
var mnth = false;
var chkweek = false;

 var get_api_records = function(coin,market,start,end,year=false,all=false) {
    
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
                    checker = true;
                    mnth = false;
                    date = date.substring(0, 10);
                }else{
                    checker = false;
                    mnth = true;
                    var date = new Date(prices[i][0]).toGMTString();
                }
                if (mnth) {
                  var pieces = date.split(" ");
                  if(date_checker != pieces[1]){
                        var saving_date = date.replace(/\s/g, '');
                        final_array[counter] = {label: date, y: prices[i][1]};
                        date_checker = pieces[1];
                        counter++;
                    }
                }
                if (checker) {
                  if(date_checker != date){
                        var saving_date = date.replace(/\s/g, '');
                        date = date.replace(/,/g, '');
                        final_array[counter] = {label: date, y: prices[i][1]};
                        date_checker = date;
                        counter++;
                    }
                }


                    
                }
            }
        });
        return final_array;
  }

});

</script>