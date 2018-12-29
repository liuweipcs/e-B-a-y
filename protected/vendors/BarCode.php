<?php
// Including all required classes
require_once dirname(__FILE__).'/barcodegen/class/BCGFont.php';
require_once dirname(__FILE__).'/barcodegen/class/BCGColor.php'; 
require_once dirname(__FILE__).'/barcodegen/class/BCGDrawing.php'; 
class BarCode{
	public function createBarCode($text,$code,$o,$t,$r,$f1,$f2,$a1,$a2,$a3){
		/*'BCGcodabar','BCGcode11','BCGcode39','BCGcode39extended','BCGcode93',
		'BCGcode128','BCGean8','BCGean13','BCGisbn','BCGi25','BCGs25','BCGmsi',
		'BCGupca','BCGupce','BCGupcext2','BCGupcext5','BCGpostnet','BCGothercode'*/
		//$codebar = $_REQUEST['codebar']; //该软件支持的所有编码，只需调整$codebar参数即可。

		// Including the barcode technology
		require_once dirname(__FILE__).'/barcodegen/class/BCG'.$code.'.barcode.php'; 
		// Loading Font
		
		if($f1 !== '0' && $f1 !== '-1' && intval($f2 >= 1)){
			$font = new BCGFontFile(dirname(__FILE__).'/barcodegen/font/'.$f1,intval($_GET['f2']));
		} else {
			$font = 0;
		}
		// The arguments are R, G, B for color.
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255); 

		$drawException = null;
		try {
			$codebar = 'BCG'.$code;
			$code = new $codebar();//实例化对应的编码格式
			if(isset($a1) && intval($a1) === 1) {
				$code->setChecksum(true);
			}
			if(isset($a2) && !empty($a2)) {
				$code->setStart($a2);
			}
			if(isset($a3) && !empty($a3)) {
				$code->setLabel($a3);
			}
			$code->setScale($r); // Resolution
			$code->setThickness($t); // Thickness
			$code->setForegroundColor($color_black); // Color of bars
			$code->setBackgroundColor($color_white); // Color of spaces
			$code->setFont($font); // Font (or 0)
			$text = $text; //条形码将要数据的内容
			$code->parse($text);
		} catch(Exception $exception) {
			$drawException = $exception;
		}

		/* Here is the list of the arguments
		1 - Filename (empty : display on screen)
		2 - Background color */
		$drawing = new BCGDrawing('', $color_white);
		if($drawException) {
			$drawing->drawException($drawException);
		} else {
			$drawing->setBarcode($code);
			$drawing->draw();
		} 
		ob_clean();  //关键代码，防止出现'图像因其本身有错无法显示'的问题
		// Header that says it is an image (remove it if you save the barcode to a file)
		if(!isset($_GET['filename'])){ //rubil
			if(intval($_GET['o']) === 1) {
				header('Content-Type: image/png');
			} elseif(intval($_GET['o']) === 2) {
				header('Content-Type: image/jpeg');
			} elseif(intval($_GET['o']) === 3) {
				header('Content-Type: image/gif');
			}
		}
		// Draw (or save) the image into PNG format.
	//	echo $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
		echo $drawing->finish(intval($o));
	}
}
?>