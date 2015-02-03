
<?php return "
#header {
    background: $colors[highlight1]
}

#x2-hidden-widgets-menu li:hover span, 
.main-menu > li:hover,
.main-menu > li.active {
    background: $colors[light_highlight1]
}

#profile-dropdown {
	color: $colors[smart_text]
}

#x2-hidden-widgets-menu,
#x2-hidden-widgets-menu:after,
#x2-hidden-widgets-menu li span, 
#header .dropdown ul,
#header .dropdown ul li a{
	background: $colors[highlight1]
	color: $colors[smart_text]
}




#header .dropdown ul li a:hover{
	background: $colors[light_highlight1]
	color: $colors[smart_text]
}

#notif-box:after {
	border-bottom-color: $colors[content]
}

#notif-box {
	background: $colors[content]
	color: $colors[text]
}

#notifs-grid .items td {
	border-color: $colors[border]
}

#notifs-grid .unviewed td,
#notifications .notif.unviewed {
	background: $colors[light_content]
}

a#main-menu-nofif span {
	color: #09f !important;
}

.three-user-menu-links {
	background: $colors[highlight1];
	border-bottom-color: $colors[darker_highlight1];
}

#top-menus-container {
	background: $colors[highlight1];
}

#header #search-bar-title {
	color: $colors[text]
}


"; ?>