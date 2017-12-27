#Yii LogAnalyzer - Log file analyzer for Yii

## Features:
- Easy connection to the project
- Output messages from the log file
- Filter log messages (Remove unwanted messages from issuance)
- Filter log output (output only error, warning or info)
- Cleaning the log file
- Multilingual (russian, english and Portuguese)

## Example:

Print out the widget in the view:

```php
<?php
$this->widget('ext.loganalyzer.LogAnalyzerWidget', array(
        'filters' => array('Text filtering','One more'),
        'title'   => 'Title of the widget' ,
        // 'log_file_path' => 'Absolute path of the Log File',
    ));  
?>
```
In addition:

Also in the expansion is extended to marshurt logs, which adds to the message logger ip client. Connect as follows:

```php
<?php
'log'=>array(
    'class'=>'CLogRouter',
    'routes'=>array(
        ....
        array(
            'class'=>'ext.loganalyzer.LALogRoute',
            'levels'=>'info, error, warning',
            ... 
        ),
        ...
    ),
),
?>
```

## Screenshot:

![Log output](https://raw.github.com/d4rkr00t/yii-loganalyzer/master/screenshot.png "Display log")

##Acknowledgments

Big thanks [Tonin De Rosso Bolzan](https://github.com/tonybolzan):

Translating to English

JavaScript Optimized:

- added effects
- confirmation on clean
- Expand/Collapse Stack Trace

PHP Code Optimized:

- removed method "processLogs()" from LALogRoute because it is equal to parent class CFileLogRoute
- changed default "log_file_path" on LogAnalyzerWidget to Yii::app()->getRuntimePath()