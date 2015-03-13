<?php return "

.data-widget svg .domain {
  stroke: $colors[text] 
}
.data-widget svg text {
  fill: $colors[text] 
}
.data-widget svg .tick line {
  stroke: $colors[text] 
}

.data-widget .config-bar .config-bar-item,
.data-widget .config-bar .config-bar-item.active {
  opacity: 0.8;
  color: $colors[text] 
}

.data-widget .config-bar .config-bar-item:hover,
.data-widget .config-bar .config-bar-item.active {
  opacity: 1.0;
  color: $colors[link] 
}

.config-bar {
  border-color: $colors[border]
}

#chart-creator .choice.active {
  border-color: $colors[highlight2] 
}
#chart-creator ::-webkit-input-placeholder {
  color: $colors[text] 
}
#chart-creator :-moz-placeholder {
  color: $colors[text] 
}
#chart-creator ::-moz-placeholder {
  color: $colors[text] 
}
#chart-creator :-ms-input-placeholder {
  color: $colors[text] 
}
#chart-creator .axis-selector {
  color: $colors[text]
  border-color: $colors[highlight2] 
}
  #chart-creator .axis-selector:hover {
    background: $colors[opaque_highlight2] 
}
  #chart-creator .axis-selector.confirmed {
    background: $colors[highlight2]
    color: $colors[smart_text2] 
}


#report-container .hover-selection {
  color: $colors[smart_text2]
  background: $colors[highlight2] 
}
#report-container .even .hover-selection {
  background: $colors[highlight2] 
}
#report-container .odd .hover-selection {
  background: $colors[light_highlight2] 
}

.chart-dashboard {
  border-color: $colors[border]
}

#chart-creator .checkbox-group .option {
  border-color: $colors[highlight2]
}

#chart-creator .checkbox-group .option.active {
  background: $colors[highlight2]
  color: $colors[smart_text2]
}

.chart-dashboard {
  border-color: $colors[highlight1]
}

"; ?>
