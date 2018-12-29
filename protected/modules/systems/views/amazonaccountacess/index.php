<?php
Yii::app()->clientscript->scriptMap['jquery.js'] = false;
?>
<div class="pageContent">

<div class="pageForm">
    <div layoutH="46" style="float:left; display:block; margin:10px; overflow:auto; width:200px;border:solid 1px #CCC;background:#FFF;resize:none;">
        <p>所有账号</p>
        <ul class="tree treeFolder collapse" id="accessJpanel">
            <?php foreach ($acntList as $value): ?>
                <li>
                    <a href="/systems/amazonaccountacess/edit/id/<?= $value['id']?>"><?=$value['name'] ?></a>

                    <?php if (isset($value['access'])): ?>

                    <ul>

                    <?php foreach($value['access'] as $v): ?>
                        <li><a href="javascript:;"><?= $v ?></a></li>
                    <?php endforeach; ?>

                    </ul>
                    <?php endif; ?>

                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="unitBox" id="boxForm" style="margin-left: 222px;margin-top:10px;border: 1px #ccc solid;resize: none;">
    </div>
</div>

</div>

<script type="text/javascript">
    $('#accessJpanel a').click(function(event){
        var This = $(this);
        var box = $('#boxForm');
        if (This.attr('href') != '' && This.attr('href') != 'javascript:;') {
            box.ajaxUrl({
                type: 'POST', 
                url : This.attr('href'),
                callback: function() {
                    box.find('[layoutH]').layoutH();
                }
            });
        }

        return false;
    });
</script>