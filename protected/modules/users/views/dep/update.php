<?php echo $this->renderPartial('_form', array('model' => $model, 'action' => 'update')); ?>
<script type="text/javascript">
    $(function() {
    	var selectedId = '<?php echo $model->parent_id; ?>',
            id = '<?php echo $model->id; ?>',
            depTreeObj = $('#dep_tree_seleced');
        setTimeout(function() {
            var selectedObj = depTreeObj.find('#dep_' + selectedId);
            $(selectedObj).parent('div').addClass('selected');
            //$('#dep_' + selectedId).closest('div').addClass('selected');
        }, 200);
        $("a", depTreeObj).click(function() {         
            var tempSelectedId = $(this).attr('id').substr(4);
            if (id == tempSelectedId) {
                alertMsg.warn('<?php echo Yii::t('users', 'The parent department cannot be the same as a child department') ?>');
                return false;
            }
            $('#Dep_parent').val(tempSelectedId);
        });
    });
</script>
