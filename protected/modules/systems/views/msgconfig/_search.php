<div class="pageHeader" style="border:1px #B8D0D6 solid">   
    <form id="pagerForm" onsubmit="return navTabSearch(this);" action="<?php echo Yii::app()->createUrl($this->route);?>" method="post">         
        <?php $this->renderPartial('application.components.views._searchHidden', array( 'pages' => $pages)); ?>
        <div class="searchBar">
            <table class="searchContent">
                <tr>                                    
                    <td>                      
                        <?php echo Yii::t('system', 'Message Code')?>：<?php echo CHtml::textField('code', @$_REQUEST['code'])?>
                    </td>
                    <td>
                        <div class="buttonActive">
                            <div class="buttonContent">
                                <button type="submit"><?php echo Yii::t('system', 'Search')?></button>
                            </div>                         
                        </div>                       
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>    