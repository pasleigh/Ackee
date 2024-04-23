/**
 * Created by cenpas on 12/07/2016.
 */
//$(function () {

    /**
     * This plugin extends Highcharts in two ways:
     * - Use HTML5 canvas instead of SVG for rendering of the heatmap squares. Canvas
     *   outperforms SVG when it comes to thousands of single shapes.
     * - Add a K-D-tree to find the nearest point on mouse move. Since we no longer have SVG shapes
     *   to capture mouseovers, we need another way of detecting hover points for the tooltip.
     */
    (function (H) {
        var Series = H.Series,
            each = H.each;

        /**
         * Create a hidden canvas to draw the graph on. The contents is later copied over
         * to an SVG image element.
         */
        Series.prototype.getContext = function () {
            if (!this.canvas) {
                this.canvas = document.createElement('canvas');
                this.canvas.setAttribute('width', this.chart.chartWidth);
                this.canvas.setAttribute('height', this.chart.chartHeight);
                this.image = this.chart.renderer.image('', 0, 0, this.chart.chartWidth, this.chart.chartHeight).add(this.group);
                this.ctx = this.canvas.getContext('2d');
            }
            return this.ctx;
        };

        /**
         * Draw the canvas image inside an SVG image
         */
        Series.prototype.canvasToSVG = function () {
            this.image.attr({ href: this.canvas.toDataURL('image/png') });
        };

        /**
         * Wrap the drawPoints method to draw the points in canvas instead of the slower SVG,
         * that requires one shape each point.
         */
        H.wrap(H.seriesTypes.heatmap.prototype, 'drawPoints', function () {

            var ctx = this.getContext();

            if (ctx) {

                // draw the columns
                each(this.points, function (point) {
                    var plotY = point.plotY,
                        shapeArgs,
                        pointAttr;

                    if (plotY !== undefined && !isNaN(plotY) && point.y !== null) {
                        shapeArgs = point.shapeArgs;

                        pointAttr = (point.pointAttr && point.pointAttr['']) || point.series.pointAttribs(point);

                        ctx.fillStyle = pointAttr.fill;
                        ctx.fillRect(shapeArgs.x, shapeArgs.y, shapeArgs.width, shapeArgs.height);
                    }
                });

                this.canvasToSVG();

            } else {
                this.chart.showLoading('Your browser doesn\'t support HTML5 canvas, <br>please use a modern browser');

                // Uncomment this to provide low-level (slow) support in oldIE. It will cause script errors on
                // charts with more than a few thousand points.
                // arguments[0].call(this);
            }
        });
        H.seriesTypes.heatmap.prototype.directTouch = false; // Use k-d-tree
    }(Highcharts));


//});

var chart;

var my_heatmap = {
    chart: {
        type: 'heatmap',
        marginTop: 40,
        marginBottom: 40
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
            },
            useHTML: true
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

