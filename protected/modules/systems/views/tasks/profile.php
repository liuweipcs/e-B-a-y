<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>

</head>
<body>
<style>
    .h2{
        padding: 10px;
        font-size: 16px;
        background: #00A2D4;
        color: #f0eff0;
        width:805px;
    }
table{

    background: none;

    border-collapse:separate; border-spacing:0px 0px;
}
    .header_start{
        background: none;
        font-size: 12px;


    }
    .header_start tr th,tr td {
        border: 1px solid #ccc;
        background: none;
        text-align: left;

    }


</style>
<div class="grid">

    <div class="gridHeader" style="background: none"><div class="gridThead">
            <h2 class="h2"> <?php echo Yii::t('system','未开始')?></h2>
            <table style="width:798px;">
                <thead class="header_start">

                    <tr style="border-collapse:separate; border-spacing:0px 10px;">

                        <th style="width: 240px;"><?php echo Yii::t('system','任务ID')?></th>
                        <th style="width: 216px;"><?php echo Yii::t('system','任务名称')?></th>
                        <th style="width: 127px;"><?php echo Yii::t('system','创建人')?></th>
                        <th style="width: 215px;"><?php echo Yii::t('system','创建时间')?></th>


                    </tr>

                </thead>
            </table>
        </div>
    </div>
    <div id="gridScroller" class="gridScroller" style="width:1703px;">
        <div class="gridTbody">
            <table style="width:798px;">
                <tbody class="header_start">
                <?php if (isset($nstart)) { ?>
                <?php   foreach($nstart as $v){ ?>
                <tr class="">
                    <td style="width: 240px;"><?php echo $v['id'];?></td>
                    <td style="width: 216px;"><?php echo $v['task_name']?></td>
                    <td style="width: 127px;"><?php echo Tasks::getUsers($v['task_create_founder']);?></td>
                    <td style="width: 215px;"><?php echo date('Y-m-d H:i:s', $v['task_create_time']);?></td>
                </tr>
                <?php }}?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="grid">

        <div class="gridHeader" style="background: none"><div class="gridThead">
                <h2 class="h2"><?php echo Yii::t('system','进行中')?></h2>
                <table style="width:798px;">
                    <thead class="header_start">
                    <tr>

                        <th class="" style="width: 240px; cursor: default;"><?php echo Yii::t('system','任务ID')?></th>
                        <th style="width: 216px;"><?php echo Yii::t('system','任务名称')?></th>
                        <th style="width: 127px;"><?php echo Yii::t('system','创建人')?></th>
                        <th style="width: 215px;"><?php echo Yii::t('system','创建时间')?></th>


                    </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div id="gridScroller" class="gridScroller" style="width:1703px;">
            <div class="gridTbody">
                <table style="width:798px;">
                    <tbody class="header_start">
                    <?php if (isset($start)) { ?>
                    <?php   foreach($start as $v){ ?>
                    <tr class="">
                        <td style="width: 240px;"><?php echo $v['id'];?></td>
                        <td style="width: 216px;"><?php echo $v['task_name']?></td>
                        <td style="width: 127px;"><?php echo Tasks::getUsers($v['task_create_founder']);?></td>
                        <td style="width: 215px;"><?php echo date('Y-m-d H:i:s', $v['task_create_time']);?></td>
                    </tr>
                    <?php }}?>
                    </tbody>
                </table>
            </div>
        </div>




</body>
</html>
