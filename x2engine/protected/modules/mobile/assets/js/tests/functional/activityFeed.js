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




(function () {

QUnit.module ("activity feed", function () {

    QUnit.test ("create feed post", function (assert) {
        // visit feed, open publisher, submit publisher form
        assert.expect (4);
        var done = assert.async (4);
        $(':mobile-pagecontainer').pagecontainer (
            'change',
            x2.main.createUrl ('/profile/mobileActivity'));

        assert.waitForPromise (function () {
                return $.mobile.activePage.attr ('data-page-id') === 'profile-mobileActivity'; 
            }, 'Page change', done)
        .then (function () {
            $.mobile.activePage.find ('.event-publisher-dummy').click ();
            return assert.waitForPromise (function () {
                return $.mobile.activePage.attr ('data-page-id') === 'profile-mobilePublisher'; 
            }, 'Page change', done); 
        })
        .then (function () {
            $.mobile.activePage.find ('.event-text-box').val ('test post');
            $.mobile.activePage.find ('.post-event-button').click ();
            return assert.waitForPromise (function () {
                return $.mobile.activePage.attr ('data-page-id') === 'profile-mobileActivity'; 
            }, 'Publisher form submit', done); 
        })
        .then (function () {
            assert.ok (
                $.trim ($.mobile.activePage.find ('.event-text').first ().text ()) === 'test post');
            done ();
        });
    });

    QUnit.test ("reply to post", function (assert) {
        assert.expect (4);
        var done = assert.async (3);

        if ($.mobile.activePage.attr ('data-page-id') !== 'profile-mobileActivity') 
            $(':mobile-pagecontainer').pagecontainer (
                'change',
                x2.main.createUrl ('/profile/mobileActivity'));

        assert.waitForPromise (function () {
                return $.mobile.activePage.attr ('data-page-id') === 'profile-mobileActivity'; 
            }, 'Page change', done)
        .then (function () {
            var items$ = $.mobile.activePage.find ('.items');
            var post$ = items$.find ('.record-list-item').first ();
            
            assert.ok (post$.length === 1);
            post$.click ();

            return assert.waitForPromise (function () {
                return $.mobile.activePage.attr ('data-page-id') === 'profile-mobileViewEvent'; 
            }, 'View post', done); 
        }).then (function () {
            var publisher$ = $.mobile.activePage.find ('form.comment-publisher-form');
            var replyCount = $.mobile.activePage.find ('.event-view .record-list-item').length;

            publisher$.find ('[type="text"]').val ('test reply');
            publisher$.find ('.submit-button').click ();

            return assert.waitFor (function () {
                return (replyCount + 1) === 
                    $.mobile.activePage.find ('.event-view .record-list-item').length;
            }, 'Add reply', done); 
        });
    });

    QUnit.test ("paginate", function (assert) {
        assert.expect (3);
        var done = assert.async (2);
        if ($.mobile.activePage.attr ('data-page-id') !== 'profile-mobileActivity') 
            $(':mobile-pagecontainer').pagecontainer (
                'change',
                x2.main.createUrl ('/profile/mobileActivity'));

        assert.waitForPromise (function () {
                return $.mobile.activePage.attr ('data-page-id') === 'profile-mobileActivity'; 
            }, 'Page change', done)
        .then (function () {
            var recordCount = $.mobile.activePage.find ('.record-list-item').length;
            assert.ok ($.mobile.activePage.find ('.more-button').length === 1);
            $.mobile.activePage.find ('.more-button').click ();
            return assert.waitFor (function () {
                return $.mobile.activePage.find ('.record-list-item').length > recordCount;
            }, 'Get more results', done); 
        })
    });

});

}) ();
