<?php
(@include_once("./config.php")) OR die("Cannot find this file to include: config.php<BR>");
(@include_once("./setup_connection.php")) OR die("Cannot find this file to include: setup_connection.php<BR>");
(@include_once("./setup_params.php")) OR die("Cannot find this file to include: setup_params.php<BR>");

(@include_once("./iqr.php")) OR die("Cannot find this file to include: iqr.php<BR>");
/**
 * Created by PhpStorm.
 * User: cenpas
 * Date: 08/07/2016
 * Time: 11:28
 */

function rand_n($min,$max, $num){
    $rand_nums = array();
    for($i = 0 ; $i < $num; $i++){
        //$rand_nums[]=mt_rand($min,$max);
        $rand_nums[]=$min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
    return $rand_nums;
}

$y0 = rand_n(700,1000,25);
$y1 = rand_n(0.5,3.5,20);

$y0 = BoxPlot5Stats($y0);
for ($i = 0; $i < count($y0); ++$i) {
    $y0[$i] = (int)$y0[$i];
}
$y0_avr = Average($y0);
$y0_mark = mt_rand(min($y0),max($y0));


$display_block = "<div id=\"container\" style=\"height: 400px; margin: auto; min-width: 400px; max-width: 600px\"></div>\n";
$display_block = "<div id='container' style='height: 580px; border-style: solid; border-width: 1px; border-color: black; padding: 5px;'></div>\n";

?>

<!DOCTYPE html>
<HTML>
<?php
(@include_once("./header.php")) OR die("Cannot find this file to include: header.php<BR>");
?>
<!-- Plotly.js -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#container').highcharts({

        chart: {
            type: 'boxplot'
        },

        title: {
            text: 'Module mark boxplots for Joe Bloggs'
        },
        subtitle: {
            text: 'Level 2, 2016. ',
            style: {
                fontSize: '12px',
                fontFamily: 'Verdana, sans-serif'
            }
        },

        legend: {
            enabled: false
        },

        xAxis: {
            categories: ['CIVE1', 'CIVE2', 'CIVE3', 'CIVE4', 'CIVE5'],
            title: {
                text: 'Module name'
            },
            labels: {
                rotation: -45,
                style: {
                    fontSize: '13px',
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },

        yAxis: {
            title: {
                text: 'Module mark (%)'
            },
            plotLines: [{
                value: 932,
                color: 'red',
                width: 1,
                label: {
                    text: 'Year \nmark',
                    align: 'right',
                    style: {
                        color: 'gray'
                    }
                }
            }],
            plotBands: [{
                color: 'rgba(255,204,204,0.1)', // Color value
                from: 850, // Start of the plot band
                to: 950, // End of the plot band
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

        series: [{
            name: 'Module marks',
            data: [
                <?php echo json_encode($y0) ?>,
                [733, 853, 939, 980, 1080],
                [714, 762, 817, 870, 918],
                [724, 802, 806, 871, 950],
                [834, 836, 864, 882, 910]
            ],
            tooltip: {
                headerFormat: '<em>Exam: {point.key}</em><br/>'
            }
        }, {
            name: 'Your mark',
            color: Highcharts.getOptions().colors[0],
            type: 'scatter',
            data: [ // x, y positions where 0 is the first category
                [0, <?php echo json_encode($y0_mark) ?>],
                [4, 855]
            ],
            marker: {
                fillColor: 'red',
                lineWidth: 1,
                lineColor: Highcharts.getOptions().colors[0]
            },
            tooltip: {
                pointFormat: 'Mark: {point.y:.1f}'//one dp
            }
        }],

        // remove the highcharts.com link
        credits: {
            enabled: false
        }


    });
});
</script>

<BODY>
<div id="wrapper">
    <div id="header">
        <span class="header"><img src="./images/chart_icon_negate_36.png" height=30px align="middle"><?php echo(" $running_title - $running_subtitle")?></span>
    </div><!-- /header -->

    <div id="content">
        <?php echo $display_block; ?>
    </div><!-- /content -->

    <div id="footer">
        <P class="footer_link">Go to the
            <a href="./index.php">index page</a>.
        </P>
    </div><!-- /footer -->

</div><!-- /wrapper -->
</BODY>
</HTML>
