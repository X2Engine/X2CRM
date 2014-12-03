<?php
// Fields in tables for which sample data is inserted that are Unix timestamps
// and hence need to be adjusted after insertion
return array(
    'x2_accounts' => array('createDate', 'lastUpdated', 'lastActivity'),
    'x2_action_timers' => array('timestamp', 'endtime'),
    'x2_actions' => array('createDate', 'completeDate', 'lastUpdated', 'dueDate'),
    'x2_anon_contact' => array('createDate', 'lastUpdated'),
    'x2_campaigns' => array('launchDate', 'createDate', 'lastUpdated', 'lastActivity'),
    'x2_changelog' => array('timestamp'),
    'x2_contacts' => array('lastUpdated', 'lastActivity', 'leadDate', 'createDate', 'closedate'),
    'x2_docs' => array('createDate', 'lastUpdated'),
    'x2_events' => array('timestamp', 'lastUpdated'),
    'x2_fingerprint' => array('createDate'),
    'x2_flows' => array('createDate', 'lastUpdated'),
    'x2_imports' => array('timestamp'),
    'x2_lists' => array('createDate', 'lastUpdated'),
    'x2_list_items' => array('opened', 'sent', 'unsubscribed', 'clicked'),
    'x2_media' => array('createDate', 'lastUpdated'),
    'x2_notifications' => array('createDate'),
    'x2_opportunities' => array('expectedCloseDate', 'createDate', 'lastUpdated', 'lastActivity'),
    'x2_products' => array('createDate', 'lastUpdated', 'lastActivity'),
    'x2_profile' => array('lastUpdated'),
    'x2_quotes' => array('expectedCloseDate', 'createDate', 'lastUpdated', 'lastActivity', 'expirationDate'),
    'x2_quotes_products' => array('createDate', 'lastUpdated', 'lastActivity'),
    'x2_services' => array('createDate', 'lastUpdated', 'lastActivity'),
    'x2_tags' => array('timestamp'),
    'x2_urls' => array('timestamp'),
    'x2_users' => array('lastUpdated'),
    'x2_x2leads' => array('createDate','lastUpdated','lastActivity'),
);
?>
