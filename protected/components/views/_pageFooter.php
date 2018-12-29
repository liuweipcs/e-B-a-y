<div class="panelBar bottompanelBar">
    <div class="pages">
        <span><?php echo Yii::t('system', 'Show'); ?></span>
        <select class="combox" name="numPerPage" 
        <?php if (isset($target)): ?> 
            <?php if ($target == 'dialog'): ?>
                    onchange="dwzPageBreak({targetType: 'dialog', numPerPage: '20'})"
                <?php else: ?>
                    onchange="navTabPageBreak({numPerPage: this.value}, '<?php echo $target; ?>')"
                <?php endif; ?> 
            <?php else: ?>
                onchange = "navTabPageBreak({numPerPage: this.value})"
            <?php endif; ?> >              
            <option value="20">20</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="200">200</option>
            <option value="500">500</option>
        </select>
        <span><?php echo Yii::t('system', 'Item') ?>，<?php echo Yii::t('system', 'Total') ?><?php echo $pages->getItemCount(); ?><?php echo Yii::t('system', 'Item') ?></span>
    </div>
    <div class="pagination" 
         <?php if (isset($target)): ?> 
             <?php if ($target == 'dialog'): ?>
                 targetType="dialog"
             <?php else: ?> 
                 rel="<?php echo $target; ?>"
             <?php endif; ?>
         <?php endif; ?>        
         <?php if (isset($targetType)): ?>
             targetType ="<?php echo $targetType; ?>"
         <?php endif; ?>
         totalCount="<?php echo $pages->getItemCount(); ?>" numPerPage="<?php echo $pages->getPageSize(); ?>" pageNumShown="10" currentPage="<?php echo $pages->getCurrentPage() + 1; ?>">
    </div>
</div>
