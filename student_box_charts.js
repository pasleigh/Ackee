/**
 * Created by cenpas on 12/07/2016.
 */


var chart;

var my_boxplots = {

    chart: {
        renderTo: 'container',
        type: 'boxplot'
    },

    title: {
        text: 'Module mark boxplots for Joe Bloggs',
        style: {
            fontSize: '13px',
            fontFamily: 'Verdana, sans-serif'
        }
    },
    /*
     subtitle: {
     text: 'Level 2, 2016. ',
     style: {
     fontSize: '12px',
     fontFamily: 'Verdana, sans-serif'
     }
     },
     */

    legend: {
        enabled: false
    },

    xAxis: {
        categories: ['CIVE1', 'CIVE2', 'CIVE3', 'CIVE4', 'CIVE5'],
        //title: {
        //    text: null
        //},
        labels: {
            rotation: -45,
            formatter: function () {
                var text = this.value,
                formatted = text.length > 22 ? text.substring(0, 22) + '...' : text;
                return '<div class="js-ellipse" style="width:100px; overflow:hidden; text-align: right;" title="' + text + '">' + formatted + '</div>';
            },
            style: {
                width: '100px',
                fontSize: '8px',
                //fontFamily: 'Verdana, sans-serif'
            },
            useHTML: true
        }
    },

    yAxis: {
        title: {
            text: 'Module mark (%)'
        },
        max: 100,
        minRange: 0,
        min: 0,

         plotLines: [{
             value: 50,
             color: 'red',
             width: 1,
             label: {
                 rotation: 90,
                 text: 'Year average',
                 align: 'right',
                 style: {
                     color: 'gray'
                 }
             }
         }]
         /*
        plotBands: [{
            color: 'rgba(255,204,204,0.3)', // Color value
            from: 55, // Start of the plot band
            to: 65, // End of the plot band
            label: {
                text: 'target mean',
                align: 'left',
                verticalAlign: 'top',
                rotation: 90,
                style: {
                    color: 'gray'
                }
            }
        }]
        */
    },

    plotOptions: {
        boxplot: {
            fillColor: '#F0F0E0',
            lineWidth: 2,
            medianColor: '#0C5DA5',
            medianWidth: 3,
            stemColor: '#A63400',
            stemDashStyle: 'dot',
            stemWidth: 1,
            whiskerColor: '#3D9200',
            whiskerLength: '20%',
            whiskerWidth: 3
        }
    },

    series: [
        {
            name: 'Module marks',
            data: [
                [5, 10, 40, 70, 91],
                [6, 20, 45, 60, 91],
                [10, 25, 50, 60, 81],
                [15, 30, 40, 70, 91],
                [2, 24, 30, 60, 91]
            ],
            tooltip: {
                headerFormat: '<em>Exam: {point.key}</em><br/>'
            }
        },
        {
            name: 'Mean',
                color: Highcharts.getOptions().colors[0],
                type: 'scatter',
                data: [ // x, y positions where 0 is the first category
                [0, 50],
                [4, 55]
            ],
            marker: {
                fillColor: '#0C5DA5',//as median or 'red',
                    lineWidth: 1,
                    lineColor: Highcharts.getOptions().colors[0],
                    symbol: 'square'
                },
            tooltip: {
                pointFormat: '{point.y:.1f}',//one dp
            }
        },
        {
            name: 'Mark',
            color: Highcharts.getOptions().colors[0],
            type: 'scatter',
            data: [ // x, y positions where 0 is the first category
                [0, 50],
                [4, 55]
            ],
            marker: {
                fillColor: 'red',//as median or 'red',
                lineWidth: 1,
                lineColor: Highcharts.getOptions().colors[0],
                symbol: 'circle'
            },
            tooltip: {
                pointFormat: '{point.y:.1f}',//one dp
            }
        }

    ],

    // remove the highcharts.com link
    credits: {
        enabled: false
    }

};

