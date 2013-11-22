<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/
?>
<head>
    <style>
        tip-title {
            text-align: center;
            font-weight: bold;
        }
        tip {
            text-align: center;
        }
        #tip-content{
            margin-right:22px;
        }
    </style>
</head>

<body>
    <span class="tip-refresh" title="Refresh Tip"></span>
    <div id="tip-content">
        <tip-title>
            <div id="tip-title">
                <?php
                echo $module." Tip";
                ?>
            </div>
        </tip-title>
        <tip>
            <div id="tip">
                <?php
                echo $tip;
                ?>
            </div>
        </tip>
    </div>
    <script>
        $(".tip-refresh").click(function() {
            $.ajax({
                url:yii.baseUrl+"/index.php/site/getTip",
                success:function(data){
                    data=JSON.parse(data);
                    $('#tip-content').fadeOut(400,function(){
                        $('#tip-title').text(data['module'] + " Tip");
                        $('#tip').text(data['tip']);
                        $('#tip-content').fadeIn();
                    });
                }
            });
        });
    </script>
</body>