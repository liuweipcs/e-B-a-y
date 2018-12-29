<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<div style="font-size:12px;color:red; margin:10px 0px 0px 10px;">
    <form name="frmBatchSettle" id="" action="<?php echo $this->createUrl('/systems/attendance/twofloorupload/');?>" target="_blank" method="post" enctype="multipart/form-data">
        <div style="font-size:14px;font-weight:bold;margin:0px 0px 20px 0px;"">请选择导入的Execl文件</div>
<input type="file" name="batchFile" value="">
<Br/>
<div style="font-size:12px;line-height: 25px;">请选择此文件的年份</div>
<select name="years" >
    <option value="2011">2011年</option>
    <option value="2012">2012年</option>
    <option value="2013">2013年</option>
    <option value="2014">2014年</option>
    <option value="2015">2015年</option>
    <option value="2016" selected="selected">2016年</option>
    <option value="2017">2017年</option>
    <option value="2018">2018年</option>
    <option value="2019">2019年</option>
    <option value="2020">2020年</option>
    <option value="2021">2021年</option>
</select>
<br/>
<div style="font-size:12px;line-height: 25px;">请选择此文件的月份</div>
<select name="month"  style="margin-bottom: 10px;">
    <option value="01" >1月</option>
    <option value="02">2月</option>
    <option value="03">3月</option>
    <option value="04">4月</option>
    <option value="05">5月</option>
    <option value="06">6月</option>
    <option value="07">7月</option>
    <option value="08">8月</option>
    <option value="09">9月</option>
    <option value="10" selected="selected">10月</option>
    <option value="11">11月</option>
    <option value="12">12月</option>
</select>
<br/>
<input type="submit" value="上传" >
</form>

</div>