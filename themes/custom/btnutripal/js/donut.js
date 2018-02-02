(function($, Drupal, drupalSettings) {
	//Tabs
	Drupal.behaviors.donut = {
		attach:function(context, settings){
		  google.charts.load('current', {'packages':['corechart']});
	      google.charts.setOnLoadCallback(drawChart);

	      function drawChart() {

	        var data = google.visualization.arrayToDataTable([
	          ['Value', 'percent'],
	          ['Protein',     40],
	          ['Fat',      33],
	          ['Carbs',    27]
	        ]);

	        var options = {
	          title: 'Nutrition Values',
	          pieHole: 0.4,
	          slices: {
                0: { color: 'red' },
                1: { color: 'green' },
                2: { color: 'blue' },
              }
	        };

	        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

	        chart.draw(data, options);
	      }
		}
	}
})(jQuery,Drupal, drupalSettings);