<?php 
/***********************************************************************************
* Copyright (c) 2016, Tom May
* Permission is hereby granted, free of charge, to any person obtaining a 
* copy of this software and associated documentation files (the "Software"),
* to deal in the Software without restriction, including without limitation the 
* rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is furnished to 
* do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be 
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY 
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, 
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE 
* OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
***********************************************************************************/
?>

<?php 
Yii::app()->clientScript->registerCss('heatMapStyle', '
    rect.bordered {
        stroke: #E6E6E6;
        stroke-width:2px;   
    }
    text.mono {
        font-size: 9pt;
        font-family: Consolas, courier;
        fill: #aaa;
    }
    text.axis-workweek {
        fill: #000;
    }
    text.axis-worktime {
        fill: #000;
    }
');

Yii::app()->clientScript->registerCss('tabStyling', '
    /* Style the tab */
    .tab {
        overflow: hidden;
        border-bottom: 1px solid #ccc;
    }

    /* Style the buttons inside the tab */
    .tab button {
        background-color: inherit;
        float: left;
        outline: none;
        cursor: pointer;
        padding: 0.1em 1em;
        transition: 0.3s;
        font-size: 17px;
        border-right: 0.5px solid #ccc;
        border-top: 0.5px solid #ccc;
        border-left: 0.5px solid #ccc;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        border-bottom: 0px;
        //height: 0.5em;
    }

    /* Change background color of buttons on hover */
    .tab button:hover {
        background-color: #ddd;
    }

    /* Style active/current tab */
    .tab button.active {
        background-color: #ccc;
    }
');

Yii::app()->clientScript->registerScript("heatMap", '
    var url = "' . Yii::app()->request->getScriptUrl() . '/marketing/marketing/getHeatMapData/?listId=' . $model->listId . '&startDate=' . $model->launchDate . '&clicks=0";
    x2.heatMap = new Object();

    x2.heatMap.redraw = function () {
        d3.select("svg").remove();
        
        // Set up margins for the grid
        var margin = {
            top: 35, 
            right: 20, 
            bottom: 50, 
            left: 23 
        };
        
        var width = Number((d3.select("#campaign-grid").node().getBoundingClientRect().width).toFixed(2));
        var height = width / 3;
        var gridSize = Math.floor(width / 25); 
        var legendElementWidth = gridSize * 2;
        var buckets = 10;
        var colors = ["#f2f2f2", "#b8e3ff","#9acdf4","#7db8e9","#63a2de","#498cd3","#3276c6","#1c60b9","#084aaa", "#003399"], // alternatively colorbrewer.YlGnBu[9]
            days = ["Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
            times = ["12a", "1a", "2a", "3a", "4a", "5a", "6a", "7a", "8a", "9a", "10a", "11a", "12p", "1p", "2p", "3p", "4p", "5p", "6p", "7p", "8p", "9p", "10p", "11p"],
            datasets = [url];

        // Draw svg
        var svg = d3.select("#campaign-heat-map").append("svg")
            .attr("width", width)
            .attr("height", height + margin.top + margin.bottom)// + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

        // Day labels
        var dayLabels = svg.selectAll(".dayLabel")
            .data(days)
            .enter().append("text")
                .text((d) => d)
                .attr("x", 0)
                .attr("y", (d, i) => i * gridSize)
                .style("text-anchor", "end")
                .attr("transform", "translate(-6," + gridSize / 1.5 + ")")
                .attr("class", "dayLabel mono axis axis-workweek");

        // Time labels
        var timeLabels = svg.selectAll(".timeLabel")
            .data(times)
            .enter().append("text")
                .text((d) => d)
                .attr("x", (d, i) => i * gridSize)
                .attr("y", 0)
                .style("text-anchor", "middle")
                .attr("transform", "translate(" + gridSize / 2 + ", -6)")
                .attr("class", "timeLabel mono axis axis-worktime");

        var type = (d) => {
            return {
                day: +d.day,
                hour: +d.hour,
                value: +d.value
            };
        };

        // Handle drawing of heat map chart
        function drawHeatMap(data) {
            heatMapValues = d3.csvParse(data["values"]);
            
            // Round legend values so 
            for(var i = 0; i < data["legendValues"].length; i++) {
                data["legendValues"][i] = Math.round(data["legendValues"][i]);
            }

            var colorScale = d3.scaleThreshold()
                .domain(data["legendValues"])
                .range(colors);

            var cards = svg.selectAll(".hour")
                .data(heatMapValues, (d) => d.day + ":" + d.hour);

            cards.append("title");

            cards.enter().append("rect")
                .attr("x", (d) => (d.hour - 1) * gridSize)
                .attr("y", (d) => (d.day - 1) * gridSize)
                .attr("rx", 4)
                .attr("ry", 4)
                .attr("class", "hour bordered")
                .attr("width", gridSize)
                .attr("height", gridSize)
                .style("fill", colors[0])
            .merge(cards)
                .transition()
                .duration(1000)
                .style("fill", (d) => colorScale(d.value));

            cards.select("title").text((d) => d.value);

            cards.exit().remove();

            var legend = svg.selectAll(".legend")
                .data([0].concat(colorScale.domain()));

            const legend_g = legend.enter().append("g")
                .attr("class", "legend");

            legend_g.append("rect")
                .attr("x", (d, i) => legendElementWidth * i)
                .attr("y", height)
                .attr("width", legendElementWidth)
                .attr("height", gridSize / 2)
                .style("fill", (d, i) => colors[i]);

            legend_g.append("text")
                .attr("class", "mono")
                .text((d) => ((Math.round(d) == 0) ? "" :  "≥ ") + Math.round(d))
                .attr("x", (d, i) => legendElementWidth * i)
                .attr("y", height + gridSize);

            legend.exit().remove();
        }
        
        // Makes call to controller function to get heat map values
        function getHeatMapData(url) {
            return $.ajax({
                url: url,
                type: "GET",
                data: {
                    listId: "' . $model->listId . '",
                    launchDate: "' . $model->launchDate . '",
                    dateRange: $("#heat-map-dates").val(),
                },
                success: function(data) {
                    data = JSON.parse(data);
                    data["values"].trim();
                    drawHeatMap(data);
                },
            });
        }

        getHeatMapData(url);
    }
      
    // Change the heat map values if the user switches tabs
    $("#heat-map-dates").change(function() {
        x2.heatMap.redraw();
    });

    // Function to handle "switching tabs" from opens to clicks
    x2.heatMap.switchTabs = function(selected, heatMapType) {
        var tabs;
        
        tabs = document.getElementsByClassName("heat-map-tab");
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].className = tabs[i].className.replace(" active", "");
        }
        
        selected.currentTarget.className += " active";

        url = url.slice(0, -1) + heatMapType;
        x2.heatMap.redraw();
    }

    // Set opens to the default heat map by simulating clicking
    // the tab
    document.getElementsByClassName("heat-map-tab")[0].click();

    // Redraw based on the new size whenever the browser window is resized.
    window.addEventListener("resize", x2.heatMap.redraw());
', CClientScript::POS_READY);
?>
<div id='campaign-heat-map' class='grid-view x2-gridview'>
    <div class="tab">
        <button class="heat-map-tab" onclick="x2.heatMap.switchTabs(event, 0)">Opens</button>
        <button class="heat-map-tab" onclick="x2.heatMap.switchTabs(event, 1)">Clicks</button>
        <button class="heat-map-tab" onclick="x2.heatMap.switchTabs(event, 2)">Sent</button>
    </div>
    <div>
        <h5>Select a date range:</h5>
        &nbsp;
        <?php
            // Determine the start of the week relative to the 
            // launch date of the campaign
            $startDate = (date('D', $model->launchDate) == 'Mon') ? strtotime('This Monday', $model->launchDate) : strtotime('Last Monday', $model->launchDate);
            $dateRanges = array();
            $tempStart = $startDate;

            // Create array of date ranges from the week the campaign is launched
            // plus another two weeks
            for($i = 0; $i < 3; $i++) {
                $dateRanges[$i] = (string) date('m/d', $tempStart) . " - ";
                $endOfWeek = strtotime('This Sunday', $tempStart);
                $dateRanges[$i] .= (string) date('m/d/y', $endOfWeek);
                $tempStart = strtotime('Next Monday', $tempStart);
            }

            // Create dropdown that will have the selectable date ranges for the campaign
            echo CHtml::dropDownList('heat-map-dates', 0, $dateRanges, array('id' => 'heat-map-dates'));
        ?>
    </div>
</div>
<button class="heat-map-refresh x2-button" id="heat-map-refresh" onclick="x2.heatMap.redraw()" title="<?php echo CHtml::encode(Yii::t('marketing','Click to refresh the heat map.')); ?>">
    <?php echo X2Html::fa('fa-refresh'); ?>
    <?php echo Yii::t('marketing', 'Refresh heat map'); ?>
</button>
