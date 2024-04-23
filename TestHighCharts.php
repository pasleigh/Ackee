<?php
	$site_url = "localhost:8080/Medita/";
	$json_script_url = $site_url."json_script.php";	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Highcharts demo</title>		
		<style type="text/css">
			.button-div
			{
				width:auto;
				height:auto;
				margin-top:20px;
			}
			.space
			{
				margin:8px;
				padding:2px;
			}
			#container
			{
				width:600px;
				height:400px;
			}
		</style>
	</head>
	<body>
		<div id="container"></div>
		<div class="button-div">
			<button id="DataSet1" class="space">Data Set 1</button>
			<button id="DataSet2" class="space">Data Set 2</button>
			<button id="DataSet3" class="space">Data Set 3</button>
			<button id="DataSet4" class="space">Data Set 4</button>
			<button id="DataSet5" class="space">Data Set 5</button>
		</div>
	</body>
	<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
	<script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

	<script src="https://code.highcharts.com/highcharts.js"></script>
	<script src="https://code.highcharts.com/highcharts-more.js"></script>
	<script src="https://code.highcharts.com/modules/exporting.js"></script>

	<script type="text/javascript">
			
			var JS_CHART=null;
			$(".space").bind('click',function(){
				var url = '<?php echo $json_script_url;?>';
				var param = $(this).attr('id');
				generateChart(url,param);
			});

			function makeCall(url,param){
				var response;
				$.ajax({url:url,data : {DataSet : param},async:false,cache:false,dataType:"json",success:
					function(data){
						response = data;
					}
				});
				return response;
			}
			function generateChart(url,param,options){
				if(url === null)
					return null;
				data = makeCall(url,param);
				if(JS_CHART===null){
					options.xAxis.categories = data[0];
					options.series = data[1];
					var chart = new Highcharts.Chart(options);
					chart.setSize(600,400);
					JS_CHART = chart;
				}else{
					removeSeriesData();
					var obj = data[1];
					JS_CHART.xAxis[0].setCategories(data[0],false);
					for(var idx in obj)
						JS_CHART.addSeries(obj[idx],false);
					JS_CHART.redraw();
				}
			}
			function removeSeriesData(){
				while(JS_CHART.series.length>0)
					JS_CHART.series[0].remove(false);
			}
			function generateChartOptions(div,title){
				var options = {
        					chart: 
						{
            						renderTo: div,
            						type: 'line',
							backgroundColor:'rgba(255, 255, 255, 1.0)',
							borderColor: '#A8BDD1',
							borderWidth: 2				
        					},
        					title: 
						{
            						text: title
        					},
        					xAxis: 
						{
            						categories: [],
            						title: 
							{
                						text: 'Months of the Year'
            						}
        					},
        					yAxis: 
						{
            						min: 0,
            						title: 
							{
                						text: 'Data usage in Gb'
            						}
        					},
        					tooltip: 
						{
            			  			formatter: function() 
							{
								var param1, param2;
								if(this.x != undefined)
									param1 = this.x;
								if(this.y != undefined)
									param2 = this.y;					

                    						return ''+ param1 +': '+ param2 +'';
                		  			}
        					},
						credits: 
						{
            						enabled: false
        					},
        					series: []
    					};
				return options;
			}
			$(document).ready(function(){
					var url = '<?php echo $json_script_url;?>';
				alert(url);
					options = generateChartOptions('container','Data Usage');
					generateChart(url,'DataSet1',options);
			});
	</script>
</html>
