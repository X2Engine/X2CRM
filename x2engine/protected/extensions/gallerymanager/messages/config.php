<?php

return array(
    'sourcePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'messagePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'messages',
    //'languages' => array('zh_cn', 'zh_tw', 'de', 'el', 'es', 'sv', 'he', 'nl', 'pt', 'pt_br', 'ru', 'it', 'fr', 'ja', 'pl', 'hu', 'ro', 'id', 'vi', 'bg', 'lv', 'sk'),
    'languages' => array('en', 'ru', 'uk', 'de'),
    'fileTypes' => array('php'),
    'overwrite' => true,
    'exclude' => array(
        '/messages',
        '/vendors',
        '/assets',
    ),
);
