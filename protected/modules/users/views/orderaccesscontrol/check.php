<?php //echo $this->renderPartial('_form', array('model' => $model, 'action' => 'update', 'departmentId' => $department_id)); ?>
<?php Yii::app()->clientscript->scriptMap['jquery.js'] = false; ?>
<div class="pageContent">
    <style>
        td {
            height: 25px;
        }
    </style>
    <?php
    $form = $this->beginWidget('ActiveForm', array(
        'id' => 'OrderaccesscontrolForm',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
        'focus' => array($model, 'user_name'),
        'clientOptions' => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType' => false,
            'afterValidate'=>'js:definedAfterValidate',
        ),
        'htmlOptions' => array(
            'class' => 'pageForm',
        )
    ));
    ?>
    <div class="pageFormContent" layoutH="58" >
        <div class="tabs" currentindex="0" eventtype="click">
            <div class="tabsHeader">
                <div class="tabsHeaderContent">
                    <ul>
                        <li class=""><a href="javascript:;"><span>已拥有账号</span></a></li>
                    </ul>
                </div>
            </div>
            <div class="tabsContent" style="height:500px;">
                <div inited="1000" style="display: block;">
                    <input name="userid" type="hidden" size="30" value="<?php echo $model->id;?>">
                    <?php $Amazonaccount = Yii::app()->db->createCommand()
                        ->select('id')
                        ->from('ueb_department')
                        ->where('department_parent_id = 5')
                        ->queryAll();
                    foreach($Amazonaccount as $k => $v){
                        $Amazonaccount[$k]=$v['id'];
                    }
                    ?>
                    <?php if($departmentId == 5 || in_array($departmentId, $Amazonaccount)){;?>
                        <p>
                            <label>亚马逊平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center;height: 25px" >
                                <tr>
                                    <td>编号</td>
                                    <td>排序</td>
                                    <td>拥有账号</td>
                                </tr>
                            <?php
                            $account = Yii::app()->db->createCommand()
                                ->select('id,account_name,sort')
                                ->from('ueb_amazon_account')
                                ->order('sort asc,id desc')
                                ->queryAll();
                            if(!empty($account))
                            {
                                $i = 1;
                                foreach ($account as $key => $value)
                                {
                                    foreach($result as $k => $v)
                                    {
                                        if($k == 'AMAZON' && in_array($value['id'], $v))
                                        {
                                            echo "<tr><td>".(int)($i)."</td><td>{$value['sort']}</td><td>{$value['account_name']}</td></tr>";
                                            $i++;
                                        }
                                    }
                                }
                            }else{
                                echo '没有找到店铺信息';
                            }
                            ?>
                            </table>
                        </div>
                    <?php }else if($departmentId == 4){;?>
                        <p>
                            <label>速卖通平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,account')
                                    ->from('ueb_aliexpress_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'ALI' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['account']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label>MALL平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,user_name')
                                    ->from('ueb_mall_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'MALL' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['user_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                    <?php }else if($departmentId == 16){;?>
                        <p>
                            <label style="width: 200px">CDISCOUNT平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,short_name')
                                    ->from('ueb_cdiscount_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'CDISCOUNT' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['short_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>
                    <?php }else if($departmentId == 9){;?>
                        <p>
                            <label>WISH平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('wish_id,account')
                                    ->from('ueb_wish_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'WISH' && in_array($value['wish_id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['account']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>
                    <?php }else if($departmentId == 15){;?>
                        <p>
                            <label>EBAY平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,user_name')
                                    ->from('ueb_ebay_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'EB' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['user_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>
                    <?php }else if($departmentId == 17){;?>
                        <p>
                            <label>Lazada平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,seller_name')
                                    ->from('ueb_lazada_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'LAZADA' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['seller_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label>SHOPEE平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,seller_name')
                                    ->from('ueb_shopee_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'SHOPEE' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['seller_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                    <?php }else{;?>
                        <p>
                            <label>亚马逊平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center;height: 25px" >
                                <tr>
                                    <td>编号</td>
                                    <td>排序</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,account_name,sort')
                                    ->from('ueb_amazon_account')
                                    ->order('sort asc,id desc')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'AMAZON' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."</td><td>{$value['sort']}</td><td>{$value['account_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label>速卖通平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,account')
                                    ->from('ueb_aliexpress_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'ALI' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['account']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label style="width: 200px">CDISCOUNT平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,short_name')
                                    ->from('ueb_cdiscount_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'CDISCOUNT' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['short_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label>WISH平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('wish_id,account')
                                    ->from('ueb_wish_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'WISH' && in_array($value['wish_id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['account']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>

                        </div>

                        <p>
                            <label>EBAY平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,user_name')
                                    ->from('ueb_ebay_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'EB' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['user_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label>Lazada平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,seller_name')
                                    ->from('ueb_lazada_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'LAZADA' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['seller_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label>SHOPEE平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,seller_name')
                                    ->from('ueb_shopee_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'SHOPEE' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['seller_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label>MALL平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,user_name')
                                    ->from('ueb_mall_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'MALL' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['user_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>

                        <p>
                            <label style="width: 200px">WALMART平台账号：</label>
                        </p>
                        <div class="row" style="margin-bottom:10px;margin-top: 10px; border:1px dashed #000; overflow:hidden;_height:10px; min-height:10px;">
                            <table width="1200" border="1"  cellspacing="0" cellpadding="0" style="text-align:center" >
                                <tr>
                                    <td>编号</td>
                                    <td>拥有账号</td>
                                </tr>
                                <?php
                                $account = Yii::app()->db->createCommand()
                                    ->select('id,account_name')
                                    ->from('ueb_walmart_account')
                                    ->queryAll();
                                if(!empty($account))
                                {
                                    $i = 1;
                                    foreach ($account as $key => $value)
                                    {
                                        foreach($result as $k => $v)
                                        {
                                            if($k == 'WALMART' && in_array($value['id'], $v))
                                            {
                                                echo "<tr><td>".(int)($i)."<td>{$value['account_name']}</td></tr>";
                                                $i++;
                                            }
                                        }
                                    }
                                }else{
                                    echo '没有找到店铺信息';
                                }
                                ?>
                            </table>
                        </div>
                    <?php };?>
                </div>
            </div>
            <div class="tabsFooter">
                <div class="tabsFooterContent"></div>
            </div>
        </div>
    </div>

    <?php $this->endWidget(); ?>
</div>
<script type="text/javascript">
</script>


