
<?php return "

.form,
#actions-frameUpdate-form,
.form-view {
	background: $colors[content];
	color: $colors[text]
	border-color: $colors[lighter_content]
}

.form-view div,
.form-view label,
.form-view td {
	border-color: $colors[lighter_content];
}

.sectionTitle {
	color: $colors[text];
}


.form-view div,
.form-view label,
.form-view textarea,
.form label,
.form input:not([type=\"submit\"]):not([type=\"button\"]),
.form .row,
.form textarea {
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

#action-list.list-view .items ,
#action-list.list-view .view {
	background: $colors[content];
	color: $colors[text];
	border-color: $colors[light_content];
}

#action-list.list-view .description {
	color: $colors[text];

}

#action-list.list-view .footer {
	color: $colors[light_text];

}

.no-border {
	background: none !important;;
}

.form2 {
	background: none !important;
}

form .formInputBox textarea{
	border-color: $colors[border]
}

form input[type='text'].error {
	color: #222 !important;
}

"; ?>