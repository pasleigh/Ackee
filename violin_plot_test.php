<?php
(@include_once("./ackee_header.php")) OR die("Cannot find this file to include: ackee_header.php<BR>");
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <title>AcKee Attendance Record Viewing</title>
    <!-- Minified Cookie Consent served from our CDN -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/1.0.9/cookieconsent.min.js"></script>
    <?php echo($ackee_bookstrap_links)?>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jstat@latest/dist/jstat.min.js"></script>
    <!--<script src="https://marketing-demo.s3-eu-west-1.amazonaws.com/violinFunction/processViolin.js"></script>-->
    <script src="./js/processViolin.js"></script>
    <style>
        #container {
            max-width: 1500px;
            min-width: 300px;
            margin: auto;
        }
        table,
        th,
        td {
            border: 0px solid;
            border-collapse: collapse;
        }
    </style>
</head>

<body>


<div class="container">

    <h1>Violin Plot Test</h1>

    <div id="container"></div>
</div>
</body>
<script>
    Highcharts.getJSON(
        "https://raw.githubusercontent.com/mekhatria/demo_highcharts/master/viloinData.json?callback=?",
        function (dataJson) {
            let rowing = [],
                taekwondo = [],
                triathlon = [],
                fencing = [];
            dataJson.forEach((elm) => {
                switch (elm.sport) {
                    case "rowing":
                        rowing.push(elm.weight);
                        break;
                    case "taekwondo":
                        taekwondo.push(elm.weight);
                        break;
                    case "triathlon":
                        triathlon.push(elm.weight);
                        break;
                    case "fencing":
                        fencing.push(elm.weight);
                        break;
                }
            });

            //Process violin data
            let step = 1,
                precision = 0.00000000001,
                width = 3;
            let data = processViolin(
                step,
                precision,
                width,
                rowing,
                taekwondo,
                triathlon,
                fencing
            );

            //Structure the data to create the chart
            let xi = data.xiData;
            let stat = data.stat;
            let violin1 = data.results[0],
                violin2 = data.results[1],
                violin3 = data.results[2],
                violin4 = data.results[3];

            Highcharts.chart("container", {
                chart: {
                    type: "areasplinerange",
                    inverted: true
                },
                title: {
                    text: "The 2012 Olympic male athletes weight"
                },
                xAxis: {
                    reversed: false,
                    labels: { format: "{value} kg" }
                },

                yAxis: {
                    title: { text: null },
                    categories: ["Rowing", "Taekwondo", "Triathlon", "Fencing"],
                    startOnTick:false,
                    endOnTick:false,
                    gridLineWidth: 0
                },
                tooltip: {
                    useHTML: true,
                    valueDecimals: 3,
                    formatter: function () {
                        return (
                            "<b>" +
                            this.series.name +
                            "</b><table><tr><td>Max:</td><td>" +
                            stat[this.series.index][4] +
                            " kg</td></tr><tr><td>Q 3:</td><td>" +
                            stat[this.series.index][3] +
                            " kg </td></tr><tr><td>Median:</td><td>" +
                            stat[this.series.index][2] +
                            " kg</td></tr><tr><td>Q 1:</td><td>" +
                            stat[this.series.index][1] +
                            " kg</td></tr><tr><td>Min:</td><td>" +
                            stat[this.series.index][0] +
                            " kg</td></tr><tr><td>Mean:</td><td>" +
                            stat[this.series.index][5] +
                            " kg</td></tr></table>"
                        );
                    }
                },
                plotOptions: {
                    series: {
                        marker: {
                            enabled: false
                        },
                        states: {
                            hover: {
                                enabled: false
                            }
                        },
                        pointStart: xi[0],
                        events: {
                            legendItemClick: function (e) {
                                e.preventDefault();
                            }
                        }
                    }
                },

                series: [
                    {
                        name: "Rowing",
                        color: "#ffa8d4",
                        data: violin1
                    },
                    {
                        name: "Taekwondo",
                        color: "#a8d4ff",
                        data: violin2
                    },
                    {
                        name: "Triathlon",
                        color: "#ffa956",
                        data: violin3
                    },
                    {
                        name: "Fencing",
                        color: "#46f15f",
                        data: violin4
                    }
                ]
            });
        }
    );

</script>
</html>
