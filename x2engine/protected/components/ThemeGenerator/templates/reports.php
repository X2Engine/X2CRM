<?php return "

#report-container {
  background: $colors[content] 
}

#content-container-inner {
  border-color: $colors[lighter_content]
  background: $colors[content] 
}

.x2-cond-list li, .x2-cond-list fieldset {
  background: $colors[bright_content] 
}

.x2-pill-box, .x2-subgrid, .x2-cond-list li {
  border-color: $colors[darker_content] 
}

#generated-report .x2-button {
  color: $colors[smart_text] 
}

.x2-pill-box {
  background: $colors[content]
  box-shadow: none !important; 
}
  .x2-pill-box:hover {
    background: $colors[bright_content] 
}

.x2-pill-box-options,
.options-header,
.x2-pill {
  background: $colors[bright_content]
  color: $colors[text]
  border-color: $colors[lighter_content]
  box-shadow: none !important; 
}
  .x2-pill-box-options:hover,
  .options-header:hover,
  .x2-pill:hover {
    background: $colors[brighter_content] 
}

ul.x2-dropdown-list {
  background: $colors[content]
  border-color: $colors[lighter_content] 
}
  ul.x2-dropdown-list li:hover:not(.x2-button){
    background: $colors[bright_content] 
}

.x2-subgrid-top-bar {
  background: $colors[highlight1] 
}

li.opt-group-header,
li.opt-group-header:hover {
  background: $colors[light_highlight1]
  border-color: $colors[border]
  color: $colors[smart_text]
}



"; ?>
