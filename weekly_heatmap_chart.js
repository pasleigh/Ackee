/**
 * Created by cenpas on 12/07/2016.
 */


var chart;

var my_heatmap = {
    chart: {
        type: 'heatmap',
        marginTop: 40,
        marginBottom: 40,
        marginLeft: 180
    },


    title: {
        text: 'Student weekly attendance'
    },

    xAxis: {
        categories: ['W0', 'W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9', 'W10', 'W11', 'W12'],
        title: "Week"
    },

    yAxis: {
        categories: ['20161234', '20161235', '20161236', '20161237', '20161238'],
        title: null,
        labels: {
            //rotation: -45,
            style: {
                width: '100px',
                fontSize: '8px'
                //fontFamily: 'Verdana, sans-serif'
            }
        }
    },

    colorAxis: {
        min: 0,
        minColor: '#FF0000',
        maxColor: '#00FF00'//Highcharts.getOptions().colors[0]
    },

    legend: {
        align: 'right',
        layout: 'vertical',
        margin: 0,
        verticalAlign: 'top',
        y: 25,
        symbolHeight: 320
    },

    tooltip: {
        formatter: function () {
            return '<b>SID: </b>' + this.series.yAxis.categories[this.point.y] + '<br><b>' +
                this.series.xAxis.categories[this.point.x] + '</b> <br> <b>Attendance </b>' +
                this.point.value;
        }
    },

    series: [{
        name: 'Sales per employee',
        borderWidth: 0,
        data: [
            [0,0,10],[0,1,19],[0,2,8],[0,3,24],[0,4,67],[1,0,92],[1,1,58],[1,2,78],[1,3,117],[1,4,48],[2,0,35],[2,1,15],[2,2,123],[2,3,64],[2,4,52],[3,0,72],[3,1,132],[3,2,114],[3,3,19],[3,4,16],[4,0,38],[4,1,5],[4,2,8],[4,3,117],[4,4,115],[5,0,88],[5,1,32],[5,2,12],[5,3,6],[5,4,120],[6,0,13],[6,1,44],[6,2,88],[6,3,98],[6,4,96],[7,0,31],[7,1,1],[7,2,82],[7,3,32],[7,4,30],[8,0,85],[8,1,97],[8,2,123],[8,3,64],[8,4,84],[9,0,47],[9,1,114],[9,2,31],[9,3,48],[9,4,91]
        ]
        /*
        ,
        dataLabels: {
            enabled: false,
            color: 'black',
            style: {
                textShadow: 'none'
            }
        }
        */
    }],

    // remove the highcharts.com link
    credits: {
        enabled: true,
        text: 'Any questions? Click here to email Andy Sleigh',
        href: 'mailto:P.A.Sleigh@leeds.ac.uk?Subject=Attendance%20charts'
    }

};

