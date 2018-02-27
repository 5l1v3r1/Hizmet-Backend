
// Get Charts' Data Functions
function getColumnChartData(the_url) {
    var return_data = [],
        arr_1 = [],
        arr_2 = [];

    $.ajax
    ({
        type:'POST',
        url:the_url,
        async:false,
        dataType: "json",
        success:function(response)
        {
            //alert(response);
            data = response[2];

            if(data){
                $.each(data, function(k,v){
                    if(this[response[0]]!="-"){
                        arr_1.push( this[response[0]] );
                        arr_2.push( parseFloat(this[response[1]]) );
                    }
                });
            }

            return_data.push( arr_1 );
            return_data.push( arr_2 );
        },
        error:function(response)
        {
            //	alert(response);
        }
    });

    return return_data;
}

function getPieChartData(the_url) {
    var return_data = [];

    var totalcount = 0, toptencount = 0, counter = 0;

    $.ajax
    ({
        type:'POST',
        url:the_url, // getting 1000 records to calculate the others portion
        async:false,
        dataType: "json",
        success:function(response)
        {
            data = response[2];

            if(data){
                $.each(data, function(k,v){
                    totalcount = totalcount + parseInt( this[response[1]] );

                    if(counter < 10){
                        toptencount = toptencount + parseInt( this[response[1]] );
                    }

                    counter = counter + 1;
                });

                counter = 0;

                $.each(data, function(k,v){
                    if(counter == 10){ // just to show 10 records on the pie chart
                        return false;
                    }

                    return_data.push([this[response[0]],parseFloat((parseInt(this[response[1]])*100/totalcount).toFixed(2))]);

                    counter = counter + 1;
                });

                otherscount = totalcount - toptencount;

                if(otherscount > 0){
                    return_data.push(["Diğerleri", parseFloat((parseInt(otherscount)*100/totalcount).toFixed(2))]);
                }
            }
        },
        error:function(response)
        {
            //	alert(response);
        }
    });

    return return_data;
}

function getLineSeriesChartData(the_url) {
    var return_data = [];
    $.ajax
    ({
        type:'POST',
        url:the_url,
        async:false,
        dataType: "json",
        success:function(response)
        {
            return_data = response;
        },
        error:function(response)
        {
            //	alert(response);
        }
    });

    return return_data;
}

// Draw Charts Functions
function drawColumnChart(the_element, the_options, the_data) {
    chrt = new Highcharts.Chart({
        chart: {
            renderTo: the_element,
            type: 'column',
            margin: [ 40, 30, 90, 75],
            borderWidth: 0,
            plotBorderWidth: 0
        },
        title: {
            text: the_options["chartTitle"],
        },
        xAxis: {
            categories: the_data[0],
            labels: {
                rotation: -30,
                align: 'right',
                style: {
                    fontSize: '10px',
                    fontFamily: 'Verdana, sans-serif'
                }
            }
        },
        yAxis: {
            min: 0,
            title: {
                text: the_options["yText"]
            }
        },
        legend: {
            enabled: false
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            column: {
                colorByPoint: true,
                pointPadding: 0.2,
                maxPointWidth: 50,
                borderWidth: 1
            }
        },
        tooltip: {
            formatter: function() {
                return  the_options["xReturn"] + ': ' + '<b>'+ this.x +'</b><br/>'+
                    the_options["yText"] + ': '+ '<b>'+ (the_options["convert_type"] == 'byte' ? humanFileSize(this.y, true) :  Highcharts.numberFormat(this.y, 0)) + '</b>'+
                    ' ';
            }
        },
        series: [{
            name: '',
            data : the_data[1],
            dataLabels: {
                enabled: true,
                rotation: 0,
                align: 'center',
                x: 0,
                y: 5,
                style: {
                    fontSize: '12px',
                    fontFamily: 'Trebuchet MS, Verdana, sans-serif'
                },
                formatter:function(){
                    if(the_options.convert_type=="byte")
                        return humanFileSize(this.y,true);
                    return this.y;

                }
            }
        }],
        exporting: {
            buttons: {
                contextButton: {
                    menuItems: [{
                        text: '\xa0\xa0\xa0 Print \xa0\xa0\xa0',
                        onclick: function () {
                            this.print();
                        },
                        separator: false
                    },{
                        text: '\xa0\xa0\xa0 PNG \xa0\xa0\xa0',
                        onclick: function () {
                            this.exportChart({
                                type: "image/png"
                            });
                        },
                        separator: false
                    }, {
                        text: '\xa0\xa0\xa0 PDF \xa0\xa0\xa0',
                        onclick: function () {
                            this.exportChart({
                                type: "application/pdf"
                            });
                        },
                        separator: false
                    }]
                }
            }
        }
    });

    if (the_data[0].length == 0 && the_data[1].length == 0) {

        h = $("#"+the_element).height() / 2;
        w = $("#"+the_element).find( ".highcharts-container" ).width() /2;

        chrt.renderer.text('Veri bulunamadı...', w-50,h)
            .attr({
                rotation: 0
            })
            .css({
                color: 'red',
                fontSize: '14px'
            })
            .add();
    }

    return chrt;

}

var colors = ['#50B432','#058DC7','#FF9655', '#FFF263', '#ED561B','#24CBE5', '#DDDF00', '#64E572', '#FF9655','#6AF9C4','#FFF263'];
function draw3DPieChart(the_element, the_options, the_data) {
    chrt = new Highcharts.Chart({
        chart: {
            renderTo: the_element,
            type: 'pie',
            borderWidth: 0,
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: the_options["chartTitle"],
        },
        colors: colors,
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                animation:false,
                shadow: false,
                depth: 35,
                center: ['50%', '50%'],
                showInLegend: false,
                dataLabels: {
                    enabled: true,
                    connectorPadding: 5,
                    format: '{point.name}',

                    defer:false
                }
            }
        },
        legend: {
            align: 'left',
            layout: 'vertical',
            verticalAlign: 'middle',
            x: 40,
            y: 20
        },
        credits: {
            enabled: false
        },
        tooltip: {
            valueSuffix: ' %',
            //pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        series: [{
            type: 'pie',
            name: 'Oran',
            colorByPoint: true,
            data:the_data,
            size: '70%',
            animation:false,
            dataLabels: {
                formatter: function() {
                    text=this.key;
                    if (text.length > 35) {
                        text= text.substring(0,35);
                    }
                    return '<b>'+ text +':</b> '+ this.y +'%';
                }
            }
        }],
        exporting: {
            buttons: {
                contextButton: {
                    menuItems: [{
                        text: '\xa0\xa0\xa0 Print\xa0\xa0\xa0',
                        onclick: function () {
                            this.print();
                        },
                        separator: false
                    },{
                        text: '\xa0\xa0\xa0 PNG \xa0\xa0\xa0 ',
                        onclick: function () {
                            this.exportChart({
                                type: "image/png"
                            });
                        },
                        separator: false
                    }, {
                        text: '\xa0\xa0\xa0 PDF\xa0\xa0\xa0',
                        onclick: function () {
                            this.exportChart({
                                type: "application/pdf"
                            });
                        },
                        separator: false
                    }]
                }
            }
        }
    });

    if (the_data.length == 0) {
        h = $('#' + the_element).height() / 2;
        w = $("#"+the_element).find( ".highcharts-container" ).width() /2;

        chrt.renderer.text('Veri bulunamadı...', w-50,h)
            .attr({
                rotation: 0
            })
            .css({
                color: 'red',
                fontSize: '14px'
            })
            .add();
    }

    return chrt;
}

function drawLineSeriesChart(the_element, the_options, the_data) {

    the_categories = [];
    if(the_data.categories!=undefined)
        the_categories = the_data.categories;

    chrt = new Highcharts.Chart({
        chart: {
            renderTo: the_element,
            type:'spline',
            zoomType: 'x',
            margin: [ 40, 30, 90, 75],
            borderWidth: 0
        },
        title: {
            text: the_options["chartTitle"],
        },
        xAxis: {
            categories: the_categories
        },
        yAxis: {
            min: 0,
            title: {
                text: the_options["yText"]
            }
        },
        tooltip: {
            formatter: function() {
                return  '<b>'+ this.x +'</b><br/>'+
                    the_options["yText"] + ': '+ '<b>'+ (the_options["convert_type"] == 'byte' ? humanFileSize(this.y, true) :  Highcharts.numberFormat(this.y, 0)) + '</b>'+
                    ' ';
            }
        },
        legend: {
            enabled: false
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            area: {
                fillColor: {
                    linearGradient: {
                        x1: 0,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops: [
                        [0, 'rgb(255, 255, 255)'],
                        [1, 'rgb(240, 240, 255)']
                    ]
                },
                marker: {
                    radius: 2
                },
                lineWidth: 1,
                states: {
                    hover: {
                        lineWidth: 1
                    }
                },
                threshold: null
            }
        },
        series: [{
            cursor: 'pointer',
            name: the_options["yText"],
            data: the_data.data
        }],
        exporting: {
            buttons: {
                contextButton: {
                    menuItems: [{
                        text: '\xa0\xa0\xa0 Print \xa0\xa0\xa0',
                        onclick: function () {
                            this.print();
                        },
                        separator: false
                    },{
                        text: '\xa0\xa0\xa0 PNG \xa0\xa0\xa0',
                        onclick: function () {
                            this.exportChart({
                                type: "image/png"
                            });
                        },
                        separator: false
                    }, {
                        text: '\xa0\xa0\xa0 PDF \xa0\xa0\xa0',
                        onclick: function () {
                            this.exportChart({
                                type: "application/pdf"
                            });
                        },
                        separator: false
                    }]
                }
            }
        }
    });

    if (the_data.data.length == 0) {

        h = $("#"+the_element).height() / 2;
        w = $("#"+the_element).find( ".highcharts-container" ).width() /2;

        chrt.renderer.text('Veri bulunamadı...', w-50,h)
            .attr({
                rotation: 0
            })
            .css({
                color: 'red',
                fontSize: '14px'
            })
            .add();
    }

    return chrt;
}

function drawNetworkChart(element,initial_value,c_title){
    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });

    $('#'+element).highcharts({
        chart: {
            type: 'spline',
            borderWidth: 0,
            plotBorderWidth: 1,
            backgroundColor: '#f3f3f4',

            animation: Highcharts.svg, // don't animate in old IE
            events: {
                load: function () {
                }
            }
        },
        title: {
            text: c_title
        },
        xAxis: {
            type: 'datetime',
            tickPixelInterval: 150,
            gridLineWidth: 0,
            lineWidth: 0,
            tickWidth: 0
        },
        yAxis: {
            title: {
                text: null
            },
            gridLineWidth: 0,
            minorGridLineWidth: 0,
            lineWidth: 0,
            labels: {
                enabled: false
            },
            tickWidth: 0
        },
        plotOptions: {
            series: {
                marker: {
                    enabled: true,
                    symbol: 'circle',
                    radius: 3
                }
            }
        },
        tooltip: {
            formatter: function () {
                return '<b>' + humanFileSize(this.y, true) + '</b><br/>' + Highcharts.dateFormat('%d.%m.%Y %H:%M:%S', this.x);
            }
        },
        legend: {
            enabled: false
        },
        exporting: {
            enabled: false
        },
        series: [{
            name: '',
            data: []
        }]
    });
}