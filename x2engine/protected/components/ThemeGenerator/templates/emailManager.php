<?php 
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




return "

#email-mini-module .bottom-row-outer {
    background: $colors[content]
}

#inline-email-form .upload-file-container {
    background: $colors[light_content]
    border-color: $colors[border]
    color: $colors[text]
}

.seen-message-row, .seen-message-row td, .seen-message-row .from-cell {
  background: $colors[content]
  border-color: $colors[border] 
}

#my-email-inbox-set-up-instructions-container{
  background: $colors[content]
  border-color: $colors[border] 
}

.unseen-message-row td, .unseen-message-row .from-cell {
  border-color: $colors[border]
  background: $colors[light_content] 
}

body.not-mobile-body #yw0, .pager, .x2grid-body-container {
  background: $colors[content]
  border-color: $colors[border] 
}

.row.ui-droppable, .row.buttons.last-button-row,
.row.buttons.last-button-row + .clearfix {
  background: $colors[content]
  border-color: $colors[border] 
}

#email-inbox-tabs .email-inbox-tab {
  background: $colors[content]
  border-color: $colors[border] 
}

.email-inputs .row {
  border-color: $colors[border] 
}

#email-quota {
  color: $colors[text] 
}

.credentials-list .credentials-view {
  color: $colors[text] 
}
.credentials-list .default-state {
  background: $colors[content]
  color: $colors[text]
  border-color: $colors[border] 
}
.credentials-list .default-state-set {
  background: $colors[highlight2]
  color: $colors[smart_text2]
  border-color: $colors[light_highlight2] 
}

#email-list .x2grid-body-container.x2grid-no-pager .items tr {
  background: $colors[content] 
}
  #email-list .x2grid-body-container.x2grid-no-pager .items tr td {
    border-color: $colors[content] 
}

.empty-text-progress-bar {
  background: $colors[highlight2] 
}

.folder-link.current-folder {
  background: $colors[light_content] 
}

.mailbox-controls {
  border-color: $colors[border] 
}

#email-mini-module .email-to-row.show-input-name::before, #cc-row.show-input-name::before, #bcc-row.show-input-name::before {
    color: $colors[text]
}

.flagged-toggle::before {
  color: $colors[darker_content] 
}
.flagged-toggle.flagged::before {
  color: $colors[darker_highlight2] 
}
.flagged-toggle.flagged::after {
  color: $colors[highlight2] 
}

#message-container {
  background: #EEE; 
}

.reply-more-menu,
.more-drop-down-list {
  background: $colors[content] 
}
  .reply-more-menu li,
  .more-drop-down-list li {
    color: $colors[text] 
}
    .reply-more-menu li:hover,
    .more-drop-down-list li:hover {
      background: $colors[light_content] 
}

#my-email-inbox-reconfigure-instructions-container {
  background: $colors[content]
  border-color: $colors[border]
}

.mailbox-controls {
  background: $colors[content]
}

.email-sync-check-box-container .right-label{
  color: $colors[text]
}

"; ?>
