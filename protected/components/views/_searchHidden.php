<?php echo CHtml::hiddenField('pageNum', $pages->getCurrentPage())?>
<?php echo CHtml::hiddenField('numPerPage', $pages->getPageSize())?>
<?php echo CHtml::hiddenField('orderField', @$_REQUEST['orderField'])?>
<?php echo CHtml::hiddenField('orderDirection', @$_REQUEST['orderDirection'])?>
<?php echo CHtml::hiddenField('target', @$_REQUEST['target'])?> 