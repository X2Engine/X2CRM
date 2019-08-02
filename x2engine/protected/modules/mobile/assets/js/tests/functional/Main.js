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




x2.tests = {};

x2.tests.main = (function () {

function Main () {
    // these values get automatically set when run in context of MobileModuleTest
    this.config = {
        username: null,
        password: null
//        username: 'admin',
//        password: '1'
        //username: 'chames',
        //password: 'password'
    };
    this.configureQUnit ();
}

Main.prototype.configureQUnit = function () {
    $('#qunit').draggable ();
    QUnit.config.reorder = false;

    /**
     * Wait for condition until timeout
     * @param function fn returns true if condition passes, false otherwise
     * @param string|function message message to print alongside assertion in test output
     * @param object done return value of QUnit.assert.async
     * @param number interval time between checks in milliseconds
     * @param number count max number of times to check condition
     */
    QUnit.assert.waitFor = function (fn, message, done, interval, count) {
        var that = this;
        message = typeof message === 'undefined' ? undefined : message; 
        if ($.type (message) === 'string') {
            message = (function (message) { return function () { return message; }; }) (message);
        }
        interval = typeof interval === 'undefined' ? 1000 : interval; 
        count = typeof count === 'undefined' ? 10 : count; 
        var id = setInterval (function () {
            if (!count--) {
                window.clearInterval (id);
                that.ok (false, message ());
                done ();
            } else if (result = fn ()) {
                window.clearInterval (id);
                that.ok (result, message ());
                done ();
            }
        }, interval);
    }

    /**
     * Same as QUnit.assert.waitFor except returns a promise 
     */
    QUnit.assert.waitForPromise = function (fn, message, done, interval, count) {
        var that = this;
        message = typeof message === 'undefined' ? undefined : message; 
        if ($.type (message) === 'string') {
            message = (function (message) { return function () { return message; }; }) (message);
        }
        interval = typeof interval === 'undefined' ? 1000 : interval; 
        count = typeof count === 'undefined' ? 10 : count; 
        return new Promise (function (resolve, reject) {
            var id = setInterval (function () {
                if (!count--) {
                    window.clearInterval (id);
                    that.ok (false, message ());
                    done ();
                    resolve ();
                } else if (result = fn ()) {
                    window.clearInterval (id);
                    that.ok (result, message ());
                    done ();
                    resolve ();
                }
            }, interval);
        });
    }

};

/**
 * Add QUnit test module for x2touch module. QUnit test module will test, for example, record index
 * and record CRUD operations.
 */
Main.prototype.addModuleTestModule = function (moduleName) {

    var supports = {
        update: true,
        'delete': true,
        create: true
    };

    if (moduleName === 'quotes') {
        supports.create = supports['delete'] = supports.update = false;
    }
    switch (moduleName) {
        case 'quotes': 
            supports.create = supports['delete'] = supports.update = false;
            break;
        case 'profile': 
            supports.create = supports['delete'] = supports.update = false;
            break;
        default:
            break;
    }

    QUnit.module (moduleName + " module", function () {

        var module = moduleName;

        QUnit.test ("index", function (assert) {
            assert.expect (3 + 4);
            var done = assert.async (4);
            if ($.mobile.activePage.attr ('data-page-id') !== module + '-mobileIndex') 
                $(':mobile-pagecontainer').pagecontainer (
                    'change',
                    x2.main.createUrl (module + '/mobileIndex'));

            assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileIndex'; 
                }, 'Page change', done)
            .then (function () {
                var recordCount = $.mobile.activePage.find ('.items .record-list-item').length;
                assert.ok ($.mobile.activePage.find ('.more-button').length === 1);
                $.mobile.activePage.find ('.more-button').click ();
                return assert.waitForPromise (function () {
                    return $.mobile.activePage.find ('.items .record-list-item').length > 
                        recordCount;
                }, 'Get more results', done); 
            })
            .then (function () {
                var searchString = $.trim (
                    $.mobile.activePage.find ('.items .record-list-item .item-name').
                        first ().text ());
                var unmatchingRecords = $.makeArray (
                    $.mobile.activePage.find ('.items .record-list-item .item-name').filter (
                        function () {
                            return !$.trim ($(this).text ()).match (new RegExp (searchString));
                        }));
                assert.ok (unmatchingRecords.length > 0);
                $('#header .search-button').click ();
                $('#header .search-box input[type="text"]').val (searchString);
                $('#header .search-box form').submit ();
                                
                // wait for zero unmatching records and a non-zero number of matching records
                return assert.waitForPromise (function () {
                    return !$.makeArray (
                        $.mobile.activePage.find ('.items .record-list-item .item-name').filter (
                            function () {
                                return !$.trim ($(this).text ()).match (new RegExp (searchString));
                            })).length &&
                    $.makeArray (
                        $.mobile.activePage.find ('.items .record-list-item .item-name').filter (
                            function () {
                                return $.trim ($(this).text ()).match (new RegExp (searchString));
                            })).length;
                }, 'search records', done); 
            }).then (function () {
                var searchString = $.trim (
                    $.mobile.activePage.find ('.items .record-list-item .item-name').
                        first ().text ());
                $('#header .search-clear-button').click ();
                assert.ok ($('#header .search-box input[type="text"]').val () === '');
                                
                // wait for a non-zero number of unmatching records and a non-zero number of 
                // matching records
                return assert.waitFor (function () {
                    return $.makeArray (
                        $.mobile.activePage.find ('.items .record-list-item .item-name').filter (
                            function () {
                                return !$.trim ($(this).text ()).match (new RegExp (searchString));
                            })).length &&
                    $.makeArray (
                        $.mobile.activePage.find ('.items .record-list-item .item-name').filter (
                            function () {
                                return $.trim ($(this).text ()).match (new RegExp (searchString));
                            })).length;
                }, 'clear search', done); 
            });
        });

        QUnit.test ("record view", function (assert) {
            assert.expect (1 + 2);
            var done = assert.async (2);
            var recordId;

            if ($.mobile.activePage.attr ('data-page-id') !== module + '-mobileIndex') {
                $(':mobile-pagecontainer').pagecontainer (
                    'change',
                    x2.main.createUrl (module + '/mobileIndex'));
            }

            assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileIndex'; 
                }, 'page change', done)
            .then (function () {
                var record$ = $.mobile.activePage.find ('.items .record-list-item').first ();
                //var recordUrl = record$.
                    //.attr ('href');
                recordId = $.mobile.activePage.find ('.items .record-list-item').first ()
                    .attr ('href').replace (/^.*\/(\d+)$/, '$1');
                assert.ok (record$.length);
                record$.click ();

                return assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileView-' +
                        recordId; 
                }, 'view record', done); 
            })
        });

        if (supports.update) {

        QUnit.test ("record update", function (assert) {
            assert.expect (4 + 5);
            var done = assert.async (5);
            var recordId;
            var recordFields = {};

            if ($.mobile.activePage.attr ('data-page-id') !== module + '-mobileIndex') 
                $(':mobile-pagecontainer').pagecontainer (
                    'change',
                    x2.main.createUrl (module + '/mobileIndex'));

            assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileIndex'; 
                }, 'page change', done)
            .then (function () {
                var record$ = $.mobile.activePage.find ('.items .record-list-item').first ();
                recordId = $.mobile.activePage.find ('.items .record-list-item').first ()
                    .attr ('href').replace (/^.*\/(\d+)$/, '$1');
                assert.ok (record$.length);
                record$.click ();

                return assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileView-' +
                        recordId; 
                }, 'view record', done); 
            })
            .then (function () {
                $('#header .edit-button').click ();

                return assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileUpdate-' +
                        recordId; 
                }, 'go to update page', done); 
            })
            .then (function () {
                // minimal changes made. finds and edits first text field on form
                // TODO: make more extensive changes, including changes to a variety of field types

                // exclude these fields since they won't appear on the record view screen
                var fieldNameBlacklist = {'firstName': true, 'lastName': true};
                var textField$ = $.mobile.activePage.find ('.field-container input[type="text"]')
                    .filter (function () {

                        console.log ($(this).attr ('name').replace (/^.*\[([^\]]*)]$/, '$1'));
                        return !fieldNameBlacklist[$(this).
                            attr ('name').replace (/^.*\[([^\]]*)]$/, '$1')];
                    }).first ();
                assert.ok (textField$.length);
                textField$.val ('test');

                recordFields[$.trim (
                    textField$.closest ('.field-container').find ('.field-label').text ())] = 
                    textField$.val ();
                $('#header .submit-button').click ();
                return assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileView-' +
                        recordId; 
                }, function () {
                    return 'view record: actual = ' + 
                        $.mobile.activePage.attr ('data-page-id') + ', expected = ' +
                        module + '-mobileView-' +
                            recordId; 
                }, done); 
            })
            .then (function () {
                for (var label in recordFields) {
                    var fields$ = $.mobile.activePage.find ('.field-label').filter (function () { 
                        return $.trim ($(this).text ()) === label;
                    });
                    assert.ok (
                        fields$.length, 'failed to find matching field in form');
                    assert.ok (
                        fields$.length === 1, 'test doesn\'t account for duplicate field labels');
                    assert.ok ($.trim (fields$.first ().next ().text ()) === recordFields[label]);
                }
                done ();
            });
        });

        }

        if (supports['delete']) {

        QUnit.test ("record delete", function (assert) {
            assert.expect (2 + 4);
            var done = assert.async (5);
            var recordId;

            if ($.mobile.activePage.attr ('data-page-id') !== module + '-mobileIndex') 
                $(':mobile-pagecontainer').pagecontainer (
                    'change',
                    x2.main.createUrl (module + '/mobileIndex'));

            assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileIndex'; 
                }, 'page change', done)
            .then (function () {
                var record$ = $.mobile.activePage.find ('.items .record-list-item').first ();
                //var recordUrl = record$.
                    //.attr ('href');
                recordId = $.mobile.activePage.find ('.items .record-list-item').first ()
                    .attr ('href').replace (/^.*\/(\d+)$/, '$1');
                assert.ok (record$.length);
                record$.click ();

                return assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileView-' +
                        recordId; 
                }, 'view record', done); 
            }).then (function () {
                $('#header a[href="#settings-menu"]').click ();
                return assert.waitForPromise (function () {
                    return $('#settings-menu').is (':visible');
                }, 'show setting menu', done); 
            }).then (function () {
                $('#settings-menu .delete-button').click ();
                return assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileIndex';
                }, 'delete record', done); 
            }).then (function () {
                var record$ = $.mobile.activePage.find ('.items .record-list-item').first ();
                var newRecordId = $.mobile.activePage.find ('.items .record-list-item').first ()
                    .attr ('href').replace (/^.*\/(\d+)$/, '$1');
                assert.ok (newRecordId !== recordId);
                done ();
            });
        });

        }

        if (supports['create']) {

        QUnit.test ("record create", function (assert) {
            assert.expect (0 + 3);
            var done = assert.async (3);
            //var recordFields = {};
            //var recordUrl = null;
            //var recordId = null;

            if ($.mobile.activePage.attr ('data-page-id') !== module + '-mobileIndex') 
                $(':mobile-pagecontainer').pagecontainer (
                    'change',
                    x2.main.createUrl (module + '/mobileIndex'));

            assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileIndex'; 
                }, 'page change', done)
            .then (function () {
                $.mobile.activePage.find ('.record-create-button').click ();
                return assert.waitForPromise (function () {
                    return $.mobile.activePage.attr ('data-page-id') === module + '-mobileCreate'; 
                }, 'go to create page', done); 
            })
            .then (function () {
                var requiredFields$ = $.mobile.activePage.find ('.x2-required');
                requiredFields$.each (function () {
                    if ($(this).attr ('type') === 'text') {
                        $(this).val ('test'); 
                    } else if ($(this).is ('select')) {
                        $(this).children ('option').last ().attr ('selected', 'selected');
                    } 
                    //recordFields[$(this).attr ('name')] = $(this).val ();
                });
                $('#header .submit-button').click ();
                return assert.waitForPromise (function () {
                    return $.mobile.activePage.hasClass ('mobile-view');
                }, 'create record', done); 
            });
        });

        }

    });

};

return new Main;

}) ();
