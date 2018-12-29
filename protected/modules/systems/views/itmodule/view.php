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
<div class="pageForm">
    <div class="pageFormCon>
tent" layoutH="58">

        <?php if ($model->source_code): ?>
        <div id="snippet" style="width: 100%">
            <pre>
                <code class="php"><?php echo str_replace(array('>', '<'), array('&gt;', '&lt;'), $model->source_code) ?></code>
            </pre>
        </div> 
        <?php endif; ?>

        <div style="width: 100%">
            <?php echo $model->description ?>
        </div>

    </div>

    <div class="formBar">
        <ul>
            <li>
                <div class="button">
                    <div class="buttonContent">
                        <button type="button" class="close"><?php echo Yii::t('system', 'Cancel')?></button>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
</div>
<script type="text/javascript" src="/js/HGjs/HG.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $('#snippet pre code').each(function(i, block) {
        hljs.highlightBlock(block);
    });
});    
</script>