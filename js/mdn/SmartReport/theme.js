Highcharts.theme = {
    chart: {
        backgroundColor: {
            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
            stops: [
                [0, 'rgb(232, 232, 232)'],
                [1, 'rgb(232, 232, 232)']
            ]
        },
        borderWidth: 0,
        plotBackgroundColor: null,
        plotShadow: false,
        plotBorderWidth: 0
    }

};

// Apply the theme
var highchartsOptions = Highcharts.setOptions(Highcharts.theme);