(function($, Drupal, drupalSettings) {
	//Tabs
	Drupal.behaviors.donut = {
		attach:function(context, settings){
		  google.charts.load('current', {'packages':['corechart']});
	      google.charts.setOnLoadCallback(drawChart);

	      function drawChart() {
	      	var fat = parseFloat(drupalSettings.nutripal.values.fat);
	      	var protein = parseFloat(drupalSettings.nutripal.values.protein);
	      	var carbohydrate = parseFloat(drupalSettings.nutripal.values.carbohydrate);

	        var data = google.visualization.arrayToDataTable([
	          ['Value', 'percent'],
	          ['Carbohydrate',    carbohydrate],
	          ['Protein',     protein],
	          ['Fat',      fat]
	        ]);

	        var options = {
	          pieHole: 0.4,
	          slices: {
            	0: { color: 'limegreen' },
            	1: { color: 'firebrick' },
            	2: { color: 'orange' },
         	  },
         	  chartArea:{left:10,top:0,right:0,width:'100%',height:'100%'}
	        };

	        var chart = new google.visualization.PieChart(document.getElementById('nutrition-donut'));

	        chart.draw(data, options);
	      }
		}
	}
})(jQuery,Drupal, drupalSettings);