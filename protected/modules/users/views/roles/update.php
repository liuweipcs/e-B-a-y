<?php echo $this->renderPartial('_form', array('model' => $model, 'action' => 'update')); ?>
<script type="text/javascript">
    $(function() {
        var selectedId = '<?php echo $model->parent; ?>',
            id = '<?php echo $model->name; ?>',
            roleTreeObj = $('#role_tree_seleced');
        setTimeout(function() {
            var selectedObj = roleTreeObj.find('#roleAccessId_' + selectedId);
            $(selectedObj).parent('div').addClass('selected');
        }, 200);
        $("a", roleTreeObj).click(function() {         
            var tempSelectedId = $(this).attr('id').substr(13);;
            if (id == tempSelectedId) {
                alertMsg.warn('<?php echo Yii::t('users', 'The parent role cannot be the same as a child role') ?>');
                return false;
            }
            $('#Role_parent').val(tempSelectedId);
        });
    });
</script>
