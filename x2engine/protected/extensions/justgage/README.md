justgage-yii
============

About
-----

JustGage for Yii is a widget for gauge javascript library JustGage http://www.justgage.com

Installation
------------

Uncompress in 'protected/extensions'

Usage
-----

To use this widget, you may insert the following code in a view:
```php
     $this->Widget('ext.justgage.JustGage', array(
        'options'=>array(
            'value' => 67, 
            'min' => 0,
            'max' => 100,
            'title' => "Visitors",
        ),
        'htmlOptions'=> array(
            'style'=>'width:200px; height:160px; margin: 0 auto;',
        ),
    ));
```

You can also use a JSON string:
```php
  $this->Widget('ext.justgage.JustGage', array(
        'options'=>'{
            "value": 67, 
            "min": 0,
            "max": 100,
            "title": "Visitors"
            "title": { "text": "Fruit Consumption" },
        }',
        'htmlOptions'=> array(
            'style'=>'width:200px; height:160px; margin: 0 auto;',
        ),
  ));
```


