/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

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
				css:[editor.config.contentsCss, CKEDITOR.getUrl(editor.skinPath + 'editor.css')],
				voiceLabel:editor.lang.format.panelVoiceLabel
			},

			init:function() {
			
				var attributes = editor.config.insertableAttributes;
			
				for(var model in attributes) {
					if(attributes.hasOwnProperty(model)) {
						this.startGroup(model);
						
						var attributeLabels = [];
						for(var key in attributes[model]) {
							if(attributes[model].hasOwnProperty(key))
								attributeLabels.push(key);
						}
						attributeLabels.sort();

						for(var i in attributeLabels) {
							this.add(attributes[model][attributeLabels[i]],attributeLabels[i],attributes[model][attributeLabels[i]]);
							// this.add('value', 'drop_text', 'drop_label');
						}
					}
				}
			},

			onClick:function(value) {
				editor.focus();
				editor.fire("saveSnapshot");
				editor.insertHtml(value);
				editor.fire("saveSnapshot");
				// console.debug(this);
			}
		});
	}

});










