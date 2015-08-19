<?php return "

tr.even {
    background: $colors[content];
}

tr.odd{
    background: $colors[light_content]
}

th {
    color: $colors[text];
}

.grid-view td {
    color: $colors[text]
}

.grid-view .summary,
.flush-grid-view .summary{
    color: $colors[text];
}

div.x2-gridview .pager, 
.grid-view .items{
    background: $colors[content];
}

.grid-view a {
	color: $colors[link]
}



.grid-view .page-title {
    background: $colors[highlight1];
    color: $colors[smart_text]
}

.x2-gridview .summary,
.x2-gridview b {
    color: $colors[smart_text]
}

.x2-gridview .summary .form.no-border select{
    margin-left: 10px;

}

.x2grid-body-container,
.grid-view table.items th { 
    background: $colors[light_content]
}

.grid-view tr.filters td, 
td input {
    background: $colors[content];
}

tr.odd, tr.even, td input {
    color: $colors[text]
}

tr.odd td, tr.even td, td input {
    border-color: $colors[opaque_text];
}

.grid-view .asc, .grid-view .desc,
.grid-view .asc a, .grid-view .desc a {
    /* temporarily removed until more subtle higlight option is available 
    background: $colors[highlight2]
    color: $colors[smart_text2]
    text-shadow: none !important;*/
    border: none;
}

.sortable-widget-container div.page-title .x2-minimal-select,
.x2grid-header-container,
thead,
thead th,
thead td {
    border-color: $colors[opaque_text]
}

ul.column-selector li ,
ul.column-selector {
    background: $colors[content]
    color: $colors[text]
}

.grid-view .active-indicator {
    background: $colors[highlight2]
    border: none !important;
}

.grid-view .inactive-indicator {
    background: none !important;
    border: none !important;
}

.x2-gridview #x2-gridview-page-title span.x2-hint {
    border-color: $colors[opaque_text]
}


.all-selected-notice b,
.select-all-notice b {
    color: black !important;

}

.all-selected-notice a,
.select-all-notice a.select-all-records-on-all-pages {
    color: darkBlue !important;   
}

.grid-view table.items {
    border-color: $colors[border]
}

.grid-view .page-title {
    border-color: $colors[border]
}


"; ?>
