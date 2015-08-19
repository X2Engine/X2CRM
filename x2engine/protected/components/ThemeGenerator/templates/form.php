
<?php return "

body.not-mobile-body .form,
#actions-frameUpdate-form {
	background: $colors[content];
	color: $colors[text]
	border-color: $colors[lighter_content]
}

.sectionTitle {
	color: $colors[text];
}


body.not-mobile-body .form label,
body.not-mobile-body .form input:not([type=\"submit\"]):not([type=\"button\"]),
body.not-mobile-body .form .row,
body.not-mobile-body .form textarea {
	background: $colors[content];
	color: $colors[text];
	border-color: $colors[border]
}

.tableWrapper{
	background: $colors[content];
	color: $colors[text];
}

div.x2-layout .formItem label {
	background: $colors[light_content]
	color: $colors[text]
}

.x2-layout {
	background: none !important;
}

.x2-layout, 
.x2-layout tr,
.x2-layout td,
.x2-layout label,
.x2-layout .leftLabel,
.x2-layout .tableWrapper,
.formSectionHeader,
.formItem,
.formSection {
	border-color: $colors[lighter_content]
}

.formSectionHeader{
	background: $colors[light_content]
}

em {
	color: $colors[text];
}

.x2-list-view.list-view .items ,
.x2-list-view.list-view .view {
	background: $colors[content];
	color: $colors[text];
	border-color: $colors[light_content];
}

.x2-list-view.list-view .description {
	color: $colors[text];

}

.x2-list-view.list-view .footer {
	color: $colors[light_text];

}

.x2-list-view.list-view .items .highlight ,
.x2-list-view.list-view .view.highlight {
	background: $colors[highlight2]
	color: $colors[smart_text2]
	border-color: $colors[lighter_highlight2]
}

.x2-list-view.list-view .view.highlight:hover{
	background: $colors[light_highlight2]
	border-color: $colors[lighter_highlight2]
}

.x2-list-view.list-view .view.highlight.clicked {
}


.no-border {
	background: none !important;;
}

form .formInputBox textarea{
	border-color: $colors[border]
}

form input[type='text'].error {
	color: #222 !important;
}

"; ?>
