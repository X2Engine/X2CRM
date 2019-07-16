/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




CKEDITOR.plugins.add('insertattributes',{
	requires:['richcombo'],
	init:function(editor) {

		if(editor.config.insertableAttributes.length < 1)
			return;

		editor.addCommand('insertAttribute',{
			exec:function(editor) {

				var timestamp = new Date();
				editor.insertHtml('{attribute!}');
			}
		});
		
		var attributeDropdown = editor.ui.addRichCombo('Attribute',{
			label:"{attribute}",
			title:"Insert Record Attribute",
			voiceLabel:"Insert Record Attribute",
			className:'cke_format',
			multiSelect:false,

			panel:{
				css: [ CKEDITOR.skin.getPath( 'editor' ) ].concat( CKEDITOR.config.contentsCss ),
				multiSelect: false,
				attributes: { 'aria-label': editor.lang.panelTitle }
			},

			init:function() {
			
				var attributes = editor.config.insertableAttributes;
			
				for(var model in attributes) { // Each model type gets its own section
					if(attributes.hasOwnProperty(model)) {
						this.startGroup(model); 
						
						var attributeLabels = [];
                        var attributeTokens = [];
						for(var key in attributes[model]) {
							if(attributes[model].hasOwnProperty(key)) {
								attributeLabels.push(key);
                            }
						}
						attributeLabels.sort();

						for(var i in attributeLabels) {
                            // Internal convention for referencing properties of
                            // editor.config.insertableAttributes:
                            // {property of insertableAttributes}-@-{attribute label}
                            //
                            // It is done this way to avoid storing the values
                            // inside attributes of HTML elements...which can
                            // break stuff (since the value can potentially
                            // include invalid characters like carriage returns.
                            // JSON encoding is not used because that too can
                            // cause problems.
                            //
                            // The following method call ("add") has arguments in the order:
                            // value, dropdown text, dropdown label
							this.add(model +'-@-'+attributeLabels[i],attributeLabels[i],attributeLabels[i]);
						}
					}
				}
			},

			onClick:function(value) {
				editor.focus();
				editor.fire("saveSnapshot");
                var modelAttribute = value.split('-@-');
				editor.insertHtml(editor.config.insertableAttributes[modelAttribute[0]][modelAttribute[1]]);
				editor.fire("saveSnapshot");
			}
		});
	}

});


