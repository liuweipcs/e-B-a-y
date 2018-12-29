<link rel="stylesheet" type="text/css" href="/js/HGjs/monokai-sublime.css">
<style type="text/css">

@import url(//fonts.googleapis.com/css?family=PT+Sans:400,700,400italic);

#snippet pre code {
    display: block;
    height: 19em;
    padding: 1em;
    overflow-y: auto;
    overflow-x: hidden;
}
.xclearfix:after{content:".";display:block;height:0;clear:both;visibility:hidden}
.xclearfix{*+height:1%;}

pre, pre code{
    font: normal 10pt Consolas, Monaco, monospace;
}
</style>
<div class="pageContent">
    <?php
    $form = $this->beginWidget('ActiveForm',array(
        'id'                     =>'itmodule-add',
        'enableAjaxValidation'   =>false,
        'enableClientValidation' =>false,
        'clientOptions'          => array(
            'validateOnSubmit' => true,
            'validateOnChange' => true,
            'validateOnType'   => false,
            'afterValidate'    =>'js:afterValidate',
        ),
        'action'      => Yii::app()->createUrl('/systems/itmodule/add'),
        'htmlOptions' => array(
            'method'  => 'post',
            'enctype' => "multipart/form-data",
            'class'   => 'pageForm',
        )
    ));
    ?>
    <div class="pageFormContent" layoutH="58">

        <div class="row xclearfix">
            <label style="width: 80px;">功能：</label>
            <?php echo CHtml::textField('module_name', '', array('class' => '', 'id' => 'module_name', 'style' => 'width:80%;')) ?>
        </div>

        <div class="row xclearfix">
            <label style="width: 80px">截图：</label>
            <?php echo CHtml::fileField('snap_shot','', array('id' =>'snap_shot', 'class' => 'uploadfile', 'style' => 'width:180px;float:left;','accept'=>".jpg,.png,.gif")) ?>
        </div>

        <div class="divider"></div>

        <div class="tabs collapse" currentIndex="0" eventType="click">
            <div class="tabsHeader">
                <div class="tabsHeaderContent">
                    <ul>
                        <li><a href="javascript:;"><span>代码</span></a></li>
                        <li><a href="javascript:;"><span>预览</span></a></li>
                        <li><a href="javascript:;"><span>描述</span></a></li>
                    </ul>
                </div>
            </div>
            <div class="tabsContent">
                <div>
                    <?php echo CHtml::textArea('source_code', '', array('class' => '', 'id' => 'sourcecode', 'style' => 'width:100%;height:350px;resize:none;')) ?>
                </div>

                <div>
                    <div id="snippet">
                        <pre>
                            <code class="php"></code>
                        </pre>
                    </div>                
                </div>

                <div>
                    <?php echo CHtml::textArea('description', '', array('class'=>'', 'id' => 'codedescription', 'style' => 'width:100%;height:350px;resize:none;')) ?>
                </div>
            </div>
            <div class="tabsFooter">
                <div class="tabsFooterContent"></div>
            </div>
        </div>

    </div>

    <div class="formBar">
        <ul>
            <li>
                <div class="buttonActive">
                    <div class="buttonContent">
                        <button type="button" onclick="submitforma()"><?php echo Yii::t('system', 'Save') ?></button>
                    </div>
                </div>
            </li>
            <li>
                <div class="button">
                    <div class="buttonContent">
                        <button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <?php $this->endWidget()?>
</div>
<script type="text/javascript" src="/js/HGjs/HG.js"></script>
<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
$(document).ready(function() {
    $('pre code').each(function(i, block) {
        hljs.highlightBlock(block);
    });

    var ke = KindEditor.create('#codedescription', {
        resizeType : 1,
        newlineTag : "br",
        afterCreate : function () { this.sync(); },
        afterBlur : function () { this.sync(); }
    });

    $('#sourcecode').change(function(event) {
        $('#snippet pre code').text($(this).val());
    })
    function unhtml(str) {
        return str ? str.replace(/[<">']/g, (a) => {
            return {
                '<': '&lt;',
                '>': '&gt;',
            }[a]
        }) : '';
    }
});
function submitforma() {
    var mn = $('#module_name').val().trim();
    if (mn) {
        $('#itmodule-add').ajaxSubmit({success : function(dta) {
            try {
                var Dta = $.parseJSON(dta);
                navTabAjaxDone(Dta);
                $.pdialog.closeCurrent();
            } catch(e){console.log(dta); }
        }});
    }
    return false;
}
</script>