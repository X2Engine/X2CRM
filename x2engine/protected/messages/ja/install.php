<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

return array (
// Install screen
'Install X2EngineCRM'=>'インストールX2EngineCRM',
'X2EngineCRM Installation'=>'X2EngineCRMインストール',
'Installation Complete'=>'完全インストール',
'This web application requires Javascript to function properly. Please enable Javascript in your web browser before continuing.'=>'このWebアプリケーションはJavaスクリプトが正確に機能することが必要です。続行する前に、WebブラウザでJavaScriptを有効にしてください。',
// Requirements check
'Cannot install X2EngineCRM'=>'X2EngineCRMをインストールすることはできません',
'required but missing'=>'が必要ですが欠落',
'Your server\'s PHP version'=>'ご使用のサーバーのPHPのバージョン',
'version 5.3 or later is required'=>'それ以降のバージョン5.3以降が必要です',
'Unfortunately, your server does not meet the minimum system requirements for installation'=>'残念ながら、あなたのサーバーは、インストールするための最小システム要件を満たしていない',
'Otherwise, contact your hosting provider.'=>'それ以外の場合は、ホスティングプロバイダにお問い合わせください。',
'If you are a system administrator of this server, refer to'=>'このサーバのシステム管理者である場合は、を参照してください。',

'Welcome to the X2EngineCRM application installer! We need to collect a little information before we can get your application up and running. Please fill out the fields listed below.'=>'X2EngineCRMアプリケーションインストーラへようこそ！我々はアプリケーションを起動して実行する前に、我々は、少し情報を収集する必要があります。以下のフィールドに記入してください。',

'X2EngineCRM Application Info'=>'X2EngineCRMアプリケーション情報',

'Application Name'=>'アプリケーション名',
'Default Language'=>'デフォルトの言語',
'Currency'=>'通貨',
'Admin Password'=>'管理者パスワード',
'Confirm Password'=>'パスワードを確認',
'Administrator Email'=>'管理者のメール',
'Please enter a valid email address.'=>'有効なメールアドレスを入力してください。',

'Database Connection Info'=>'データベース接続情報',

'This release only supports MySQL. Please create a database before installing.'=>'このリリースではMySQLのみをサポートしています。インストールする前にデータベースを作成してください。',
'Host Name'=>'ホスト名',
'Database Name'=>'データベース名',
'Username'=>'ユーザー名',
'Password'=>'パスワード',

// Software updates
'Software Updates'=>'ソフトウェアアップデート',
'Notify me of software updates'=>'ソフトウェアの最新情報を知らせて！',
'First Name'=>'ファーストネーム',
'Last Name'=>'姓',
'Email'=>'メール',
'Company'=>'会社',
'Position'=>'位置',
'How you found X2EngineCRM'=>'あなたはX2Engineを見つけた方法',
'Phone Number'=>'電話番号',
'Subscribe to the newsletter'=>'ニュースレターを購読する',
'Comments'=>'注釈',
'Request a follow-up contact'=>'フォローアップの接触を要求する',
'Software Updates'=>'ソフトウェアアップデート',
'Please help us improve X2EngineCRM by providing the following information:'=>'私たちは、次の情報を提供することにより、X2EngineCRMを改善する手助けをしてください：',
'Software update notifications enabled.'=>'ソフトウェア更新通知が有効になっています。',
'Optional Information'=>'オプションの情報',
'Could not connect to the updates server at this time.'=>'この時点でアップデートサーバーに接続できませんでした。',
'You can continue installing the application without enabling updates and try again later by going into "General Settings" under the section "App Settings" in the Admin console.'=>'あなたは、更新を有効にせずにアプリケーションのインストールを続行し、Adminコンソールのセクション"アプリケーションの設定"の "一般設定"に入ることによって、後でもう一度試してみることができます。',

'Install'=>'インストール',

'For help or more information - X2Engine.com'=>'ヘルプまたは詳細については、X2Engine.com',

'All Rights Reserved.'=>'すべての内容は著作権を有します。',

// Splash screen
'Installation Complete!'=>'インストールが完了します！',
'Click here to log in to X2Engine'=>'X2Engineにログインするためにここをクリック',
'X2Engine successfully installed on your web server!  You may now log in with username "admin" and the password you provided during the install.'=>'X2Engineは正常にWebサーバーにインストール！これで、ユーザ名"admin"とインストール中に指定したパスワードでログインできます。',
'If you chose to install Gii, you can find it <a href="index.php/gii/">here</a>. The password is the same as your admin password.'=>'あなたがGIIのインストールを選択した場合、あなたは見つけることができるそれ<a href="index.php/gii/">ここ</ a>。パスワードは、管理者パスワードと同じです。',

);