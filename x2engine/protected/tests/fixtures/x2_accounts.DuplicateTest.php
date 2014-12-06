<?php

return array(
    // Base Account
    'account1' => array(
        'id' => 1,
        'name' => 'Black Mesa',
        'nameId' => 'Black Mesa_1',
        'website' => 'www.blackmesa.com',
        'tickerSymbol' => 'MESA',
        'assignedTo' => 'Anyone',
        'dupeCheck' => 0,
    ),
    // Identical to Account 1
    'account2' => array(
        'id' => 2,
        'name' => 'Black Mesa',
        'nameId' => 'Black Mesa_2',
        'website' => 'www.blackmesa.com',
        'tickerSymbol' => 'MESA',
        'assignedTo' => 'Anyone',
        'dupeCheck' => 0,
    ),
    // Same name as Account 1, different other information
    'account3' => array(
        'id' => 3,
        'name' => 'Black Mesa',
        'nameId' => 'Black Mesa_3',
        'website' => 'www.black-mesa.com',
        'tickerSymbol' => 'BMSA',
        'assignedTo' => 'Anyone',
        'dupeCheck' => 0,
    ),
    // Same symbol as Account 1, different other information
    'account4' => array(
        'id' => 4,
        'name' => 'Red Mesa',
        'nameId' => 'Red Mesa_4',
        'website' => 'www.redmesa.com',
        'tickerSymbol' => 'MESA',
        'assignedTo' => 'Anyone',
        'dupeCheck' => 0,
    ),
    // Same website as Account 1, different other information
    'account5' => array(
        'id' => 5,
        'name' => 'Black Mesa Inc',
        'nameId' => 'Black Mesa Inc_1',
        'website' => 'www.blackmesa.com',
        'tickerSymbol' => 'BLKM',
        'assignedTo' => 'Anyone',
        'dupeCheck' => 0,
    ),
    // Unique Account
    'account6' => array(
        'id' => 6,
        'name' => 'Aperture Science',
        'nameId' => 'Aperture Science_6',
        'website' => 'www.aperture.com',
        'tickerSymbol' => 'PRTL',
        'assignedTo' => 'Anyone',
        'dupeCheck' => 0,
    ),
);
?>