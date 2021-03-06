function report(period) {
    if (period == "D") {
        document.getElementById('month').style.display = 'inline-block';
    } else {
        document.getElementById('month').style.display = 'none';
    }
}

window.onload = function() {
    var options = {
        chart: {
            renderTo: 'container',
            defaultSeriesType: 'spline'
        },
        credits: {
            enabled: false
        },
        title: {
            text: ''
        },
        yAxis: {
            min: 0,
            allowDecimals: false,
            title: {
                text: 'Total Enlistments'
            }
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        data: {
            table: 'monthlyData'
        }
    };
    var chart = new Highcharts.Chart(options);

    $("#list").on('change', function () {
        //alert('f')
        var selVal = $("#list").val();
        var monVal = $("#month").val();
        if (selVal == "A" || selVal == '') {
            options.chart = {renderTo: 'container', defaultSeriesType: 'spline'};
            options.data = {table: 'monthlyData'};
        }
        else if (selVal == "B") {
            options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
            options.data = {table: 'yearlyData'};
        }
        else if (selVal == "C") {
            options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
            options.data = {table: 'quarterlyData'};
        }
        else if (selVal == "D") {
            if (monVal == "jan" || monVal == "") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'janRecruiterData'};
            } else if (monVal == "feb") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'febRecruiterData'};
            } else if (monVal == "mar") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'marRecruiterData'};
            } else if (monVal == "apr") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'aprRecruiterData'};
            } else if (monVal == "may") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'mayRecruiterData'};
            } else if (monVal == "jun") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'junRecruiterData'};
            } else if (monVal == "jul") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'julRecruiterData'};
            } else if (monVal == "aug") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'augRecruiterData'};
            } else if (monVal == "sep") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'sepRecruiterData'};
            } else if (monVal == "oct") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'octRecruiterData'};
            } else if (monVal == "nov") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'novRecruiterData'};
            } else if (monVal == "dec") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'decRecruiterData'};
            }
        }
        var chart = new Highcharts.Chart(options);
    });

    $("#month").on('change', function () {
        //alert('f')
        var selVal = $("#list").val();
        var monVal = $("#month").val();
        if (selVal == "A" || selVal == '') {
            options.chart = {renderTo: 'container', defaultSeriesType: 'spline'};
            options.data = {table: 'monthlyData'};
        }
        else if (selVal == "B") {
            options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
            options.data = {table: 'yearlyData'};
        }
        else if (selVal == "C") {
            options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
            options.data = {table: 'quarterlyData'};
        }
        else if (selVal == "D") {
            if (monVal == "jan" || monVal == "") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'janRecruiterData'};
            } else if (monVal == "feb") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'febRecruiterData'};
            } else if (monVal == "mar") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'marRecruiterData'};
            } else if (monVal == "apr") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'aprRecruiterData'};
            } else if (monVal == "may") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'mayRecruiterData'};
            } else if (monVal == "jun") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'junRecruiterData'};
            } else if (monVal == "jul") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'julRecruiterData'};
            } else if (monVal == "aug") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'augRecruiterData'};
            } else if (monVal == "sep") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'sepRecruiterData'};
            } else if (monVal == "oct") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'octRecruiterData'};
            } else if (monVal == "nov") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'novRecruiterData'};
            } else if (monVal == "dec") {
                options.chart = {renderTo: 'container', defaultSeriesType: 'column'};
                options.data = {table: 'decRecruiterData'};
            }
        }
        var chart = new Highcharts.Chart(options);
    });
}

/**
 * Dark theme for Highcharts JS
 * @author Torstein Honsi
 */

Highcharts.theme = {
    colors: ["#2b908f", "#90ee7e", "#f45b5b", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
        "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
    chart: {
        backgroundColor: 'transparent',
        plotBorderColor: '#606063'
    },
    title: {
        style: {
            color: '#E0E0E3',
            textTransform: 'uppercase',
            fontSize: '20px'
        }
    },
    subtitle: {
        style: {
            color: '#E0E0E3',
            textTransform: 'uppercase'
        }
    },
    xAxis: {
        gridLineColor: '#707073',
        labels: {
            style: {
                color: '#E0E0E3',
                fontSize: '15px'
            }
        },
        lineColor: '#707073',
        minorGridLineColor: '#505053',
        tickColor: '#707073',
        title: {
            style: {
                color: '#A0A0A3'

            }
        }
    },
    yAxis: {
        gridLineColor: '#707073',
        labels: {
            style: {
                color: '#E0E0E3'
            }
        },
        lineColor: '#707073',
        minorGridLineColor: '#505053',
        tickColor: '#707073',
        tickWidth: 1,
        title: {
            style: {
                color: '#A0A0A3',
                fontSize: '15px'
            }
        }
    },
    tooltip: {
        backgroundColor: 'rgba(0, 0, 0, 0.85)',
        style: {
            color: '#F0F0F0'
        }
    },
    plotOptions: {
        series: {
            dataLabels: {
                color: '#B0B0B3'
            },
            marker: {
                lineColor: '#333'
            }
        },
        boxplot: {
            fillColor: '#505053'
        },
        candlestick: {
            lineColor: 'white'
        },
        errorbar: {
            color: 'white'
        }
    },
    legend: {
        itemStyle: {
            color: '#E0E0E3'
        },
        itemHoverStyle: {
            color: '#FFF'
        },
        itemHiddenStyle: {
            color: '#606063'
        }
    },
    credits: {
        style: {
            color: '#666'
        }
    },
    labels: {
        style: {
            color: '#707073'
        }
    },

    drilldown: {
        activeAxisLabelStyle: {
            color: '#F0F0F3'
        },
        activeDataLabelStyle: {
            color: '#F0F0F3'
        }
    },

    navigation: {
        buttonOptions: {
            symbolStroke: '#DDDDDD',
            theme: {
                fill: '#505053'
            }
        }
    },

    // scroll charts
    rangeSelector: {
        buttonTheme: {
            fill: '#505053',
            stroke: '#000000',
            style: {
                color: '#CCC'
            },
            states: {
                hover: {
                    fill: '#707073',
                    stroke: '#000000',
                    style: {
                        color: 'white'
                    }
                },
                select: {
                    fill: '#000003',
                    stroke: '#000000',
                    style: {
                        color: 'white'
                    }
                }
            }
        },
        inputBoxBorderColor: '#505053',
        inputStyle: {
            backgroundColor: '#333',
            color: 'silver'
        },
        labelStyle: {
            color: 'silver'
        }
    },

    navigator: {
        handles: {
            backgroundColor: '#666',
            borderColor: '#AAA'
        },
        outlineColor: '#CCC',
        maskFill: 'rgba(255,255,255,0.1)',
        series: {
            color: '#7798BF',
            lineColor: '#A6C7ED'
        },
        xAxis: {
            gridLineColor: '#505053'
        }
    },

    scrollbar: {
        barBackgroundColor: '#808083',
        barBorderColor: '#808083',
        buttonArrowColor: '#CCC',
        buttonBackgroundColor: '#606063',
        buttonBorderColor: '#606063',
        rifleColor: '#FFF',
        trackBackgroundColor: '#404043',
        trackBorderColor: '#404043'
    },

    // special colors for some of the
    legendBackgroundColor: 'rgba(0, 0, 0, 0.5)',
    background2: '#505053',
    dataLabelsColor: '#B0B0B3',
    textColor: '#C0C0C0',
    contrastTextColor: '#F0F0F3',
    maskColor: 'rgba(255,255,255,0.3)'
};

// Apply the theme
Highcharts.setOptions(Highcharts.theme);
