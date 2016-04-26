
<?php return "

ul.main-menu > li > a, ul.main-menu > li > span, #your-logo.icon-x2-logo-square {
    color: $colors[smart_text]
    text-shadow: none !important;
}

div.page-title, 
div.page-title h2 {
    color: $colors[smart_text];
}

#content:not(.no-backdrop) {
    background: ".preg_replace ('/!important/', '', $colors['content'])."
    border-color: ".preg_replace ('/!important/', '', $colors['lighter_content'])."
}


#x2-gridview-page-title {
    border-color: $colors[lighter_content]
}

div.page-title {
    background-color: $colors[highlight1]
    border-color: $colors[border]
}

body {
    background: $colors[background] 
    background: radial-gradient( $colors[background_hex], $colors[darker_background_hex] ) !important;
}

a {
    color: $colors[link]
}

#more-menu > *, #profile-dropdown > * {
    color: $colors[link]
    color: $colors[smart_text]
}

#login-form a.text-link, a.text-link:hover {
    color: $colors[text]
}

a:hover {
    color: $colors[lighter_link]
}


.portlet-decoration, .widget-title-bar {
    background: $colors[highlight1];
}


.portlet-title, .widget-title {
    color: $colors[smart_text]
}

.portlet-content {
    background: $colors[content]
    color: $colors[text];
}

.sortable-widget-container {
    background: $colors[content]
}



.sidebar-left, 
.portlet,
.sortable-widget-container, 
#profile-info-container, 
#activity-feed-container, 
.x2-layout-island {
    border-color: $colors[lighter_content]
}



.x2-hint {
    color: $colors[text]
}

span.tag {
    background: none !important;
    border-color: $colors[lighter_content]
}

body.not-mobile-body #footer{
    background: $colors[light_content];
    color: $colors[text]
}


.scheme-container.active {
    border-color: $colors[text]
}

iframe {
    background: white !important;
    border-radius: 2px;
}

"; ?>
