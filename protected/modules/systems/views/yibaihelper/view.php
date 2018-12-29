
<div class="pageContent"  >
    <div class="tabs">
   
        <div class="tabsContent" id="newContent">
            <div class="pageFormContenthelp"  style="border:1px solid #B8D0D6 ;padding:50px;overflow: auto !important;" >
			    <h1 id="title" style="text-align: center;font-size: 17px; padding-top: 20px;color: #333;">
				<?php echo $model->title;?>
				</h1>
				<div class="info" style="text-align: center;margin-bottom: 20px;
				padding-top: 20px;">
					 <?php echo $model->created_at;?>
					<span id="source">
					来源：<?php echo MHelper::getUsername($model->author_id);?>
					</span>
				</div>
				<div id="newContentdd" style="padding-bottom:400px;"  >
					<?php echo $model->content;?>
				</div>
            </div>
        </div>
    </div>

</div>

<style> 
.dialog .dialogContent {
   
    /*overflow: auto !important; */
   
}
.ke-zeroborder{border:1px solid #b8d0d6} 
.ke-zeroborder td{border:1px solid #b8d0d6} 




</style> 



<script>
$('.pageFormContenthelp').height($(window).height()-185);
$.fn.extend({   
    disableSelection: function() {   
        this.each(function() {   
            if (typeof this.onselectstart != 'undefined') {  
                this.onselectstart = function() { return false; };  
            } else if (typeof this.style.MozUserSelect != 'undefined') {  
                this.style.MozUserSelect = 'none';  
            } else {  
                this.onmousedown = function() { return false; };  
            }  
        });   
    }   
});  

var dialogContent  =document.getElementsByClassName("dialogContent"); 
// console.log(dialogContent);
 // dialogContent[0].style.overflow='auto';
$(document).ready(function() {  
    $('#newContent').disableSelection();              
});   

function imgdragstart(){return false;}
    for(i in document.images)document.images[i].ondragstart=imgdragstart;
  var div = document.getElementsByClassName("pageFormContenthelp");
  
  
  // for(var i=0;i<kecontainer.length;i++){
	// kecontainer[i].style.backgroundColor="";
  // }
  $(".pageFormContenthelp").each(function(){
    $(this).css('background','none');  
  });
</script>