/**
 * Created by cenpas on 12/07/2016.
 */


var chart;

var my_columnplot = {

    chart: {
        renderTo: 'container'
    },

    title: {
        text: 'Module attendance XXX eng for session 2020',
        style: {
            fontSize: '10px',
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
            text: 'Attendance (%)'
        },
        max: 100,
        minRange: 0,
        min: 0,

    },

    tooltip: {
        useHTML: true,
        headerFormat: '<em>Date: {point.key}</em><br/>',
        formatter: function(){
            var text;
            if(this.series.name == 'Attended'){
                text = '<em>Date: ' + this.x + '</em><br/>';
                if(this.point.y > 94.95) {
                    text = '<b>Present </b>';// +this.point.y;
                }else if(this.point.y > 94.85){
                    text = '<b>Present <BR>(Cluster check-in) </b>';// +this.point.y;
                }else{
                    text = '<b>Absent </b>';// +this.point.y;
                }
            }else {
                text = '<em>' + this.x + '</em><br/>' + 'Attendance: <b>' + this.point.y + '%</b><BR/>' + 'Class size: <b>' + this.point.num + '</b>';
            }
            return  text;
        }
    },

    series: [
        {
            type: 'column',
            name: 'Attendance %',
            data: [
                [2, 24, 30, 60, 91]
            ]
        },{
            type: 'line',
            name: 'Attended',
            data: [
                [90, 90, 30, 60, 91]
            ],
            lineWidth: 0,
            states: {
                hover: {
                    lineWidthPlus: 0
                }
            }
        }
    ],

    // remove the highcharts.com link
    credits: {
        enabled: true,
        text: 'Any questions? Click here to email Andy Sleigh',
        href: 'mailto:P.A.Sleigh@leeds.ac.uk?Subject=Attendance%20charts'
    }

};

