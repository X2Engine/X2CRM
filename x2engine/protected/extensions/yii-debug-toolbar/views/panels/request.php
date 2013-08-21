    <h4 class="collapsible">Request Server Parameters</h4>
    <table id="debug-toolbar-globals-server">
        <thead>
            <tr>
                <th><?php echo YiiDebug::t('Name')?></th>
                <th><?php echo YiiDebug::t('Value')?></th>
            </tr>
        </thead>
        <tbody>
            <?php $c=0; foreach ($server as $key=>$value) : ?>
            <tr class="<?php echo ($c%2?'odd':'even') ?>">
                <th><?php echo $key; ?></th>
                <td><?php echo $this->dump($value); ?></td>
            </tr>
            <?php ++$c; endforeach;?>
        </tbody>
    </table>

    <?php if ($cookies) : $c=0;?>
    <h4 class="collapsible">Request Cookies</h4>
    <table id="debug-toolbar-globals-cookies">
        <thead>
            <tr>
                <th><?php echo YiiDebug::t('Name')?></th>
                <th><?php echo YiiDebug::t('Value')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cookies as $key=>$value) : ?>
            <tr class="<?php echo ($c%2?'odd':'even') ?>">
                <th><?php echo $key; ?></th>
                <td><?php echo $this->dump($value); ?></td>
            </tr>
            <?php ++$c; endforeach;?>
        </tbody>
    </table>
    <?php else : ?>
    <h4>COOKIES Variables</h4>
    <?php echo YiiDebug::t('No Cookies')?>
    <?php endif; ?>


    <?php if ($session) : $c=0; ?>
    <h4 class="collapsible">Session Attributes</h4>
    <table id="debug-toolbar-globals-session">
        <thead>
            <tr>
                <th><?php echo YiiDebug::t('Name')?></th>
                <th><?php echo YiiDebug::t('Value')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($session as $key=>$value) : ?>
            <tr class="<?php echo ($c%2?'odd':'even') ?>">
                <th><?php echo $key; ?></th>
                <td><?php echo $this->dump($value); ?></td>
            </tr>
            <?php ++$c; endforeach;?>
        </tbody>
    </table>
    <?php else : ?>
    <h4>Session Attributes</h4>
    <?php echo YiiDebug::t('No session attributes')?>
    <?php endif; ?>

    <?php if ($get) : $c=0; ?>
    <h4 class="collapsible">Request GET Parameters</h4>
    <table id="debug-toolbar-globals-get">
        <thead>
            <tr>
                <th><?php echo YiiDebug::t('Name')?></th>
                <th><?php echo YiiDebug::t('Value')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($get as $key=>$value) : ?>
            <tr class="<?php echo ($c%2?'odd':'even') ?>">
                <th><?php echo $key; ?></th>
                <td><?php echo $this->dump($value); ?></td>
            </tr>
            <?php ++$c; endforeach;?>
        </tbody>
    </table>
    <?php else : ?>
    <h4>Request GET Parameters</h4>
    <?php echo YiiDebug::t('No GET parameters')?>
    <?php endif; ?>

    <?php if ($post) : $c=0; ?>
    <h4 class="collapsible">Request POST Parameters</h4>
    <table id="debug-toolbar-globals-post">
        <thead>
            <tr>
                <th><?php echo YiiDebug::t('Name')?></th>
                <th><?php echo YiiDebug::t('Value')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($post as $key=>$value) : ?>
            <tr class="<?php echo ($c%2?'odd':'even') ?>">
                <th><?php echo $key; ?></th>
                <td><?php echo $this->dump($value); ?></td>
            </tr>
            <?php ++$c; endforeach;?>
        </tbody>
    </table>
    <?php else : ?>
    <h4>Request POST Parameters</h4>
    <?php echo YiiDebug::t('No POST parameters')?>
    <?php endif; ?>


    <?php if ($files) : $c=0; ?>
    <h4 class="collapsible">Request FILES</h4>
    <table id="debug-toolbar-globals-files">
        <thead>
            <tr>
                <th><?php echo YiiDebug::t('Name')?></th>
                <th><?php echo YiiDebug::t('Value')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $key=>$value) : ?>
            <tr class="<?php echo ($c%2?'odd':'even') ?>">
                <th><?php echo $key; ?></th>
                <td><?php echo $this->dump($value); ?></td>
            </tr>
            <?php ++$c; endforeach;?>
        </tbody>
    </table>
    <?php else : ?>
    <h4>Request FILES</h4>
    <?php echo YiiDebug::t('No FILES data')?>
    <?php endif; ?>
