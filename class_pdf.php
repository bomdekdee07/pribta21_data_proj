<?
	require('assets/fpdf/fpdf.php');
	define('FPDF_FONTPATH','assets/fpdf/font/');
	class PDF extends FPDF {
		function SetThaiFont() {
			$this->AddFont('THSarabun','','THSarabun.php');
			$this->AddFont('THSarabun','B','THSarabun Bold.php');
		}
		
		function conv($string) {
			return iconv('UTF-8', 'TIS-620//TRANSLIT', $string);
		}
		function Footer(){
			$this->SetFont('THSarabun','',15);
		}
		function getThai($sStr){
			return iconv('UTF-8', 'tis-620//TRANSLIT', $sStr);
		}
		function tText($iX,$iY,$sStr){
			$this->Text($iX,$iY,iconv('UTF-8', 'tis-620//TRANSLIT', $sStr));
		}
		function tCell($iX, $iY, $sStr, $iBorder, $iLine, $sOrient,$fill=false){
			$this->Cell($iX, $iY, iconv('UTF-8', 'tis-620//TRANSLIT', $sStr),$iBorder, $iLine, $sOrient,$fill);
		}
		function tMultiCell($iX, $iY, $sStr, $iBorder, $sOrient,$fill=false){
			$this->MultiCell($iX, $iY, iconv('UTF-8', 'tis-620//TRANSLIT', $sStr),$iBorder,$sOrient,$fill);
		}
		function SetDash($black=null, $white=null)
		{
			if($black!==null)
				$s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
			else
				$s='[] 0 d';
			$this->_out($s);
		}



	}
?>