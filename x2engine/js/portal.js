/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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



(function () {

	$(document).ready(function() {
		let input = '#message-txt';
		let button = '#message-btn';
		let logout = '#logout';

		$('#case-listview-container').on('click', '.case-link', function(){
			console.log(this.id);
			let id = $(this).text().replace(/\D/g,'');
			window.location.search = '?' + $.param({
				servId: id
			});
		});

		$(input).keyup(function(){
			if ($(this).val()) {
				$(button).removeClass('disabled');
			} else {
				$(button).addClass('disabled');
			}
		});

		$(button).click(function(){
			if ($(this).hasClass('disabled')) return;
			$(this).addClass('disabled');

			let url = yii.baseUrl + '/index.php/services/newReply';
			let text = $(input).val();

			let that = this;
			let failureMsg = 'Message could not be sent. Please try again later.'
			$.post(url, {
				'ServiceReplies': {'serviceId': info.servId, 'text': text},
				[info.csrfName] : info.csrf,
			}).done(function (data) {
				if (data) { 
					result = JSON.parse(data);
					if (!$.isNumeric(result)) {
						console.log('action errors: ', result);
						alert(failureMsg);
					} else {
						$.fn.yiiListView.update('reply-listview');
						$.fn.yiiListView.update('case-listview');
						$(input).val('');
					}
				} else {
					console.log('empty response');
					alert(failureMsg);
				}
			}).fail(function (data, textStatus, errorThrown) {
				console.log('post fail: ', data, textStatus, errorThrown);
				alert(failureMsg);
			})
		});

		$(logout).click(function(){
			window.location.href = yii.baseUrl + '/index.php/site/logout';
		});

	});

})();