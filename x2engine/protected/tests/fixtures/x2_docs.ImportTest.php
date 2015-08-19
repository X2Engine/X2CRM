<?php
return array(
    '0' => array (
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
        'createdBy' => 'Anyone',
        'createDate' => '1367966539',
        'updatedBy' => 'Anyone',
        'lastUpdated' => '1367966539',
        'visibility' => '1',
    ),
    '1' => array (
        'id' => '53',
        'name' => 'First Doc',
        'nameId' => 'First Doc_53',
        'subject' => NULL,
        'emailTo' => NULL,
        'type' => '',
        'associationType' => NULL,
        'text' => '<html>
<head>
	<title></title>
</head>
<body>This is a sample<br />
Testing Document<br />
<br />
&nbsp;
<div style="text-align: center;"><span style="color:#FF0000;">FOR INTERNAL USE ONLY</span></div>

<div>&nbsp;</div>

<div>&nbsp;</div>
</body>
</html>
',
        'createdBy' => 'admin',
        'createDate' => '1410995212',
        'updatedBy' => 'admin',
        'lastUpdated' => '1410995212',
        'visibility' => '1',
    ),
    '2' => array (
        'id' => '54',
        'name' => 'Quote Template',
        'nameId' => 'Quote Template_54',
        'subject' => 'Testing Quote Template',
        'emailTo' => NULL,
        'type' => 'quote',
        'associationType' => NULL,
        'text' => '<html>
<head>
	<title></title>
</head>
<body>Quote #{Quote.id}<br />
{Quote.dateCreated}<br />
{Contact.lastName}, {Contact.firstName}<br />
{Account.name}<br />
<br />
<br />
Thank you for your purchase of {Quote.total}</body>
</html>
',
        'createdBy' => 'admin',
        'createDate' => '1410995351',
        'updatedBy' => 'admin',
        'lastUpdated' => '1410995351',
        'visibility' => '1',
    ),
    '3' => array (
        'id' => '55',
        'name' => 'Test Email Template',
        'nameId' => 'Test Email Template_55',
        'subject' => 'Welcome Message',
        'emailTo' => '',
        'type' => 'email',
        'associationType' => 'Contacts',
        'text' => '<html>
<head>
	<title></title>
</head>
<body>Welcome, {firstName}<br />
<br />
Thank you for joining!</body>
</html>
',
        'createdBy' => 'admin',
        'createDate' => '1410995471',
        'updatedBy' => 'admin',
        'lastUpdated' => '1410995471',
        'visibility' => '1',
    ),
);
?>
