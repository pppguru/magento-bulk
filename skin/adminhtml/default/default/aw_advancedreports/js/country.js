var AW_AR_Country = Class.create({
    initialize: function (objName) {
        window[objName] = this;
    },
    drawChart: function (values) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Country');
        data.addColumn('number', 'Percent');
        data.addRows(values);
        var options = {};
        var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
});
new AW_AR_Country('awarcountry');
