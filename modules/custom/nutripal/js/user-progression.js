(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.NutripalBehavior = {
        attach: function (context, settings) {
            // can access setting from 'drupalSettings';

            google.charts.load('current', {'packages': ['corechart']});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var rows = drupalSettings.nutripal.values.progression;
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Date');
                data.addColumn('number', 'Weight');
                if ("undefined" != typeof rows[0][2]) {
                    data.addColumn('number', 'Target Weight')
                }

                console.log(rows);

                for (var i = 0; i < rows.length; i++) {
                    //console.log(rows[i]);
                    //var date = new Date(rows[i][0] * 1000);
                    data.addRows([rows[i]]);
                    /*data.addRows(
                     [rows[i][0], rows[i][1]]
                     )*/
                }

                var options = {
                    title: 'User progression',
                    curveType: 'function',
                    legend: {position: 'bottom'}
                };

                var chart = new google.visualization.LineChart(document.getElementById('line_charts_user'));

                chart.draw(data, options);
            }
        }
    }
})(jQuery, Drupal, drupalSettings);