<?php
return array(
'0' => array (
  'id' => '51',
  'name' => 'Quote Follow Up',
  'nameId' => 'Quote Follow Up_51',
  'subject' => 'Campbell\'s Computing Quote Follow Up',
  'emailTo' => NULL,
  'type' => 'email',
  'associationType' => 'Contacts',
  'text' => '<html>
<head>
	<title></title>
</head>
<body><span style="background-color: rgb(250, 250, 250); font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: bold;">Hello&nbsp;{name},</span>
<p style="margin: 0px 0px 1.5em; padding: 0px; border: 0px; font-weight: bold; font-size: 13px; font-family: Arial, Helvetica, sans-serif; vertical-align: baseline; background-color: rgb(250, 250, 250);"><br />
I want to confirm that you received the quote I had sent over for the services you inquired about from Campbell&#39;s Cloud Computing. Please let me know if you have any questions or if you would like to schedule a phone call.<br />
<br />
Thank you for considering Campbell&#39;s Cloud!</p>

<p style="margin: 0px 0px 1.5em; padding: 0px; border: 0px; font-weight: bold; font-size: 13px; font-family: Arial, Helvetica, sans-serif; vertical-align: baseline; background-color: rgb(250, 250, 250);">&nbsp;</p>

<p style="margin: 0px 0px 1.5em; padding: 0px; border: 0px; font-weight: bold; font-size: 13px; font-family: Arial, Helvetica, sans-serif; vertical-align: baseline; background-color: rgb(250, 250, 250);">Cheers!<br />
<br />
Campbell&#39;s Cloud Team</p>
</body>
</html>
',
  'createdBy' => 'admin',
  'createDate' => '1427829382',
  'updatedBy' => 'admin',
  'lastUpdated' => '1427829382',
  'visibility' => NULL,
),
'1' => array (
  'id' => '52',
  'name' => 'Sample Quote Template',
  'nameId' => 'Sample Quote Template_52',
  'subject' => NULL,
  'emailTo' => NULL,
  'type' => 'quote',
  'associationType' => NULL,
  'text' => '<html>
<head>
    <title></title>
</head>
<body style="width:1000px">
<table cellpadding="1" cellspacing="1" style="width:100%">
    <tbody>
        <tr>
            <td><span style="font-size: 36px;">Sales {Quote.quoteOrInvoice}: {Quote.name}</span></td>
            <td style="text-align: right;">{Quote.quoteOrInvoice} #{Quote.id}<br />
            {Quote.createDate}</td>
        </tr>
    </tbody>
</table>

<table>
    <tbody>
        <tr>
            <td style="text-align: left">Customer:</td>
            <td style="text-align: left; font-weight: bold;">{Contact.name}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style="text-align: left;">{Contact.address}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style="text-align: left;">{Contact.city},&nbsp;{Contact.state}&nbsp;{Contact.zipcode}</td>
        </tr>
    </tbody>
</table>

<hr />{Quote.description}<br /><br />
{Quote.lineItems}</body>
</html>',
  'createdBy' => 'admin',
  'createDate' => '1395366229',
  'updatedBy' => 'Anyone',
  'lastUpdated' => '1395366229',
  'visibility' => '1',
),
'2' => array (
  'id' => '53',
  'name' => 'Follow Up re: That Thing I Sent You',
  'nameId' => 'Follow Up re: That Thing I Sent You_53',
  'subject' => 'Follow Up re: That Thing I Sent You',
  'emailTo' => '',
  'type' => 'email',
  'associationType' => 'Contacts',
  'text' => '<html>
<head>
	<title></title>
</head>
<body>Dear {firstName},<br />
<br />
Did you get that thing I sent you?<br />
<br />
{signature}</body>
</html>
',
  'createdBy' => 'testuser',
  'createDate' => '1427882508',
  'updatedBy' => 'testuser',
  'lastUpdated' => '1428015216',
  'visibility' => '1',
),
'3' => array (
  'id' => '54',
  'name' => 'X2Community',
  'nameId' => 'X2Community_54',
  'subject' => NULL,
  'emailTo' => NULL,
  'type' => '',
  'associationType' => NULL,
  'text' => '<html>
<head>
	<title></title>
</head>
<body><iframe frameborder="0" height="900" name="x2community-iframe" scrolling="yes" src="http://x2community.com/" width="100%"></iframe></body>
</html>
',
  'createdBy' => 'admin',
  'createDate' => '1427995741',
  'updatedBy' => 'admin',
  'lastUpdated' => '1427995780',
  'visibility' => '1',
),
'4' => array (
  'id' => '55',
  'name' => 'X2Engine Editions',
  'nameId' => 'X2Engine Editions_55',
  'subject' => NULL,
  'emailTo' => NULL,
  'type' => '',
  'associationType' => NULL,
  'text' => '<html>
<head>
	<title></title>
</head>
<body><iframe frameborder="0" height="900" name="x2engine-editions-iframe" scrolling="yes" src="http://www.x2engine.com/plans-editions/" width="100%"></iframe></body>
</html>
',
  'createdBy' => 'admin',
  'createDate' => '1427995877',
  'updatedBy' => 'admin',
  'lastUpdated' => '1427995911',
  'visibility' => '1',
),
);
?>
