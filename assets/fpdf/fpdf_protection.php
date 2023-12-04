<?php
/****************************************************************************
* Software: FPDF_Protection                                                 *
* Version:  1.06                                                            *
* Date:     2023-01-01                                                      *
* Author:   Klemen VODOPIVEC                                                *
* License:  FPDF                                                            *
*                                                                           *
* Thanks:  Cpdf (http://www.ros.co.nz/pdf) was my working sample of how to  *
*          implement protection in pdf.                                     *
****************************************************************************/

require('fpdf.php');

if(function_exists('openssl_encrypt'))
{
	function RC4($key, $data)
	{
		return openssl_encrypt($data, 'RC4-40', $key, OPENSSL_RAW_DATA);
	}
}
else
{
	function RC4($key, $data)
	{
		static $last_key, $last_state;

		if($key != $last_key)
		{
			$k = str_repeat($key, ceil(256/strlen($key)));
			$state = range(0, 255);
			$j = 0;
			for ($i=0; $i<256; $i++){
				$t = $state[$i];
				$j = ($j + $t + ord($k[$i])) % 256;
				$state[$i] = $state[$j];
				$state[$j] = $t;
			}
			$last_key = $key;
			$last_state = $state;
		}
		else
			$state = $last_state;

		$len = strlen($data);
		$a = 0;
		$b = 0;
		$out = '';
		for ($i=0; $i<$len; $i++){
			$a = ($a+1) % 256;
			$t = $state[$a];
			$b = ($b+$t) % 256;
			$state[$a] = $state[$b];
			$state[$b] = $t;
			$k = $state[($state[$a]+$state[$b]) % 256];
			$out .= chr(ord($data[$i]) ^ $k);
		}
		return $out;
	}
}

class FPDF_Protection extends FPDF
{
	const SCALE = 72 / 25.4;

	protected $encrypted = false;
	protected $padding;
	protected $encryption_key;
	protected $Uvalue;             //U entry in pdf document
	protected $Ovalue;             //O entry in pdf document
	protected $Pvalue;             //P entry in pdf document
	protected $enc_obj_id;         //encryption object id

	var $setAutoTopMargin;
	var $HTMLHeaderE;
	var $HTMLHeader;
	var $mirrorMargins;
	var $margin_header;
	var $orig_tMargin;
	var $saveHTMLHeader;
	var $pageoutput;
	var $pagenumPrefix;
	var $pagenumSuffix;
	var $aliasNbPgGp;
	var $nbpgPrefix;
	var $nbpgSuffix;
	var $aliasNbPg;
	var $HTMLheaderPageLinks;
	var $HTMLheaderPageAnnots;
	var $HTMLheaderPageForms;
	var $pageBackgrounds;
	var $writingHTMLheader;
	var $blk;
	var $headerbuffer;
	var $colorConverter;
	var $PDFAXwarnings;
	var $colorarray;
	var $spanbgcolorarray;
	var $spanbgcolor;
	var $spanborder;
	var $spanborddet;
	var $textparam;
	var $textvar;
	var $OTLtags;
	var $textshadow;
	var $currentLang;
	var $default_lang;
	var $default_available_fonts;
	var $default_font;
	var $default_font_size;
	var $currentfontfamily;
	var $currentfontsize;
	var $currentfontstyle;
	var $tableLevel;
	var $table;
	var $blklvl;
	var $lSpacingCSS;
	var $wSpacingCSS;
	var $fixedlSpacing;
	var $minwSpacing;
	var $dash_on;
	var $dotted_on;
	var $divwidth;
	var $divheight;
	var $cellTextAlign;
	var $cellLineHeight;
	var $cellLineStackingStrategy;
	var $cellLineStackingShift;
	var $oldy;
	var $cssManager;
	var $col;
	var $PDFA;
	var $PDFX;
	var $PDFAauto;
	var $PDFXauto;
	var $extgstates;
	var $available_unifonts;
	var $lineheight;
	var $shrin_k;
	var $normalLineheight;
	var $useFixedNormalLineHeight;
	var $adjustFontDescLineheight;
	var $sizeConverter;
	var $fontsizes;
	var $autoLangToFont;
	var $usingCoreFont;
	var $languageToFont;
	var $useAdobeCJK;
	var $fonttrans;
	var $onlyCoreFonts;
	var $sans_fonts;
	var $serif_fonts;
	var $mono_fonts;
	var $inlineDisplayOff;
	var $bodyBackgroundColor;
	var $ColActive;
	var $baselineSup;
	var $baselineSub;
	var $useKerning;
	var $fontLanguageOverride;
	var $original_default_font_size;
	var $defaultCSS;
	var $original_default_font;
	var $watermark_font;
	var $borderstyles;
	var $defaultPageNumStyle;
	var $PageNumSubstitutions;
	var $textbuffer;
	var $fixedPosBlockSave;
	var $allow_charset_conversion;
	var $charset_in;
	var $lastblocklevelchange;
	var $pgwidth;
	var $blockContext;
	var $directionality;
	var $bodyBackgroundGradient;
	var $bodyBackgroundImage;
	var $page_box;
	var $orig_lMargin;
	var $DeflMargin;
	var $orig_rMargin;
	var $DefrMargin;
	var $orig_bMargin;
	var $orig_hMargin;
	var $orig_fMargin;
	var $margin_footer;
	var $show_marks;
	var $firstPageBoxHeader;
	var $firstPageBoxFooter;
	var $tag;
	var $tabSpaces;
	var $autoScriptToLang;
	var $pageHTMLheaders;
	var $pageHTMLfooters;
	var $checkSIP;
	var $checkSMP;
	var $checkCJK;
	var $biDirectional;
	var $enabledtags;
	var $mb_enc;
	var $subPos;
	var $inMeter;
	var $inFixedPosBlock;
	var $fixedPosBlock;
	var $ignorefollowingspaces;
	var $ispre;
	var $OTLdata;
	var $otl;
	var $fontCache;
	var $tts;
	var $ttz;
	var $tta;
	var $selectoption;
	var $tdbegin;
	var $blockjustfinished;
	var $cell;
	var $row;
	var $decimal_align;
	var $tbctr;
	var $nestedtablejustfinished;
	var $linebreakjustfinished;
	var $outerblocktags;
	var $innerblocktags;
	var $fixedPosBlockDepth;
	var $fixedPosBlockBBox;
	var $allow_html_optional_endtags;
	var $uniqstr;
	var $internallink;
	var $bufferoutput;
	var $pregRTLchars;
	var $pregCJKchars;
	var $useSubstitutions;
	var $specialcontent;
	var $useActiveForms;
	var $lastoptionaltag;
	var $logger;
	var $ignore_invalid_utf8;
	var $entsearch;
	var $entsubstitute;
	var $basepath;
	var $basepathIsLocal;
	var $defaultAlign;
	var $defaultTableAlign;
	var $img_dpi;
	var $floatDivs;
	var $CurrCol;
	var $table_rotate;
	var $kwt;
	var $keep_block_together;
	var $cellBorderBuffer;
	var $simpleTables;
	var $packTableData;
	var $floatmargins;
	var $flowingBlockAttr;
	var $objectbuffer;
	var $MarginCorrection;
	var $ColWidth;
	var $ColGap;
	var $y0;
	var $formobjects;
	var $floatbuffer;
	var $breakpoints;
	var $ReqFontStyle;
	var $upperCase;
	var $smCapsScale;
	var $smCapsStretch;
	var $current_layer;
	var $gradient;
	var $patterns;
	var $writingHTMLfooter;
	var $forcePortraitHeaders;
	var $forcePortraitMargins;
	var $cMarginL;
	var $cMarginR;
	var $font;
	var $NbCol;
	var $margBuffer;
	var $charspacing;
	var $ChangeColumn;
	var $pregCURSchars;
	var $CJKoverflow;
	var $justifyB4br;
	var $allowCJKoverflow;
	var $CJKforceend;
	var $chunkorder;
	var $jSmaxWordLast;
	var $jSmaxCharLast;
	var $maxPosR;
	var $maxPosL;
	var $decimal_offset;
	var $visibility;
	var $bcor;
	var $useFixedTextBaseline;
	var $baselineC;
	var $ColL;
	var $ColR;
	var $ColDetails;

	/**
	* Function to set permissions as well as user and owner passwords
	*
	* - permissions is an array with values taken from the following list:
	*   copy, print, modify, annot-forms
	*   If a value is present it means that the permission is granted
	* - If a user password is set, user will be prompted before document is opened
	* - If an owner password is set, document can be opened in privilege mode with no
	*   restriction if that password is entered
	*/
	function SetProtection($permissions=array(), $user_pass='', $owner_pass=null)
	{
		$options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32 );
		$protection = 192;
		foreach($permissions as $permission)
		{
			if (!isset($options[$permission]))
				$this->Error('Incorrect permission: '.$permission);
			$protection += $options[$permission];
		}
		if ($owner_pass === null)
			$owner_pass = uniqid(rand());
		$this->encrypted = true;
		$this->padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
						"\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
		$this->_generateencryptionkey($user_pass, $owner_pass, $protection);
	}

/****************************************************************************
*                                                                           *
*                              Private methods                              *
*                                                                           *
****************************************************************************/

	function _putstream($s)
	{
		if ($this->encrypted)
			$s = RC4($this->_objectkey($this->n), $s);
		parent::_putstream($s);
	}

	function _textstring($s)
	{
		if (!$this->_isascii($s))
			$s = $this->_UTF8toUTF16($s);
		if ($this->encrypted)
			$s = RC4($this->_objectkey($this->n), $s);
		return '('.$this->_escape($s).')';
	}

	/**
	* Compute key depending on object number where the encrypted data is stored
	*/
	function _objectkey($n)
	{
		return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
	}

	function _putresources()
	{
		parent::_putresources();
		if ($this->encrypted) {
			$this->_newobj();
			$this->enc_obj_id = $this->n;
			$this->_put('<<');
			$this->_putencryption();
			$this->_put('>>');
			$this->_put('endobj');
		}
	}

	function _putencryption()
	{
		$this->_put('/Filter /Standard');
		$this->_put('/V 1');
		$this->_put('/R 2');
		$this->_put('/O ('.$this->_escape($this->Ovalue).')');
		$this->_put('/U ('.$this->_escape($this->Uvalue).')');
		$this->_put('/P '.$this->Pvalue);
	}

	function _puttrailer()
	{
		parent::_puttrailer();
		if ($this->encrypted) {
			$this->_put('/Encrypt '.$this->enc_obj_id.' 0 R');
			$this->_put('/ID [()()]');
		}
	}

	/**
	* Get MD5 as binary string
	*/
	function _md5_16($string)
	{
		return md5($string, true);
	}

	/**
	* Compute O value
	*/
	function _Ovalue($user_pass, $owner_pass)
	{
		$tmp = $this->_md5_16($owner_pass);
		$owner_RC4_key = substr($tmp,0,5);
		return RC4($owner_RC4_key, $user_pass);
	}

	/**
	* Compute U value
	*/
	function _Uvalue()
	{
		return RC4($this->encryption_key, $this->padding);
	}

	/**
	* Compute encryption key
	*/
	function _generateencryptionkey($user_pass, $owner_pass, $protection)
	{
		// Pad passwords
		$user_pass = substr($user_pass.$this->padding,0,32);
		$owner_pass = substr($owner_pass.$this->padding,0,32);
		// Compute O value
		$this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);
		// Compute encyption key
		$tmp = $this->_md5_16($user_pass.$this->Ovalue.chr($protection)."\xFF\xFF\xFF");
		$this->encryption_key = substr($tmp,0,5);
		// Compute U value
		$this->Uvalue = $this->_Uvalue();
		// Compute P value
		$this->Pvalue = -(($protection^255)+1);
	}

	// New custom function
	function SetColor($col, $type = '')
	{
		$out = '';
		if (!$col) {
			return '';
		} // mPDF 6
		if ($col{0} == 3 || $col{0} == 5) { // RGB / RGBa
			$out = sprintf('%.3F %.3F %.3F rg', ord($col{1}) / 255, ord($col{2}) / 255, ord($col{3}) / 255);
		} elseif ($col{0} == 1) { // GRAYSCALE
			$out = sprintf('%.3F g', ord($col{1}) / 255);
		} elseif ($col{0} == 2) { // SPOT COLOR
			$out = sprintf('/CS%d cs %.3F scn', ord($col{1}), ord($col{2}) / 100);
		} elseif ($col{0} == 4 || $col{0} == 6) { // CMYK / CMYKa
			$out = sprintf('%.3F %.3F %.3F %.3F k', ord($col{1}) / 100, ord($col{2}) / 100, ord($col{3}) / 100, ord($col{4}) / 100);
		}
		if ($type == 'Draw') {
			$out = strtoupper($out);
		} // e.g. rg => RG
		elseif ($type == 'CodeOnly') {
			$out = preg_replace('/\s(rg|g|k)/', '', $out);
		}
		return $out;
	}

	function SetTColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Text');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->TextColor = $out;
		$this->ColorFlag = ($this->FillColor != $out);
	}

	function SetDColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Draw');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->DrawColor = $out;
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['DrawColor']) && $this->pageoutput[$this->page]['DrawColor'] != $this->DrawColor) || !isset($this->pageoutput[$this->page]['DrawColor']))) {
			$this->_out($this->DrawColor);
		}
		$this->pageoutput[$this->page]['DrawColor'] = $this->DrawColor;
	}

	function SetFColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Fill');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->FillColor = $out;
		$this->ColorFlag = ($out != $this->TextColor);
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['FillColor']) && $this->pageoutput[$this->page]['FillColor'] != $this->FillColor) || !isset($this->pageoutput[$this->page]['FillColor']))) {
			$this->_out($this->FillColor);
		}
		$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
	}

	function AddExtGState($parms)
	{
		$n = count($this->extgstates);
		// check if graphics state already exists
		for ($i = 1; $i <= $n; $i++) {
			if (count($this->extgstates[$i]['parms']) == count($parms)) {
				$same = true;
				foreach ($this->extgstates[$i]['parms'] as $k => $v) {
					if (!isset($parms[$k]) || $parms[$k] != $v) {
						$same = false;
						break;
					}
				}
				if ($same) {
					return $i;
				}
			}
		}
		$n++;
		$this->extgstates[$n]['parms'] = $parms;
		return $n;
	}

	function SetAlpha($alpha, $bm = 'Normal', $return = false, $mode = 'B')
	{
		// alpha: real value from 0 (transparent) to 1 (opaque)
		// bm:    blend mode, one of the following:
		//          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
		//          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
		// set alpha for stroking (CA) and non-stroking (ca) operations
		// mode determines F (fill) S (stroke) B (both)
		if (($this->PDFA || $this->PDFX) && $alpha != 1) {
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) {
				$this->PDFAXwarnings[] = "Image opacity must be 100% (Opacity changed to 100%)";
			}
			$alpha = 1;
		}
		$a = ['BM' => '/' . $bm];
		if ($mode == 'F' || $mode == 'B') {
			$a['ca'] = $alpha; // mPDF 5.7.2
		}
		if ($mode == 'S' || $mode == 'B') {
			$a['CA'] = $alpha; // mPDF 5.7.2
		}
		$gs = $this->AddExtGState($a);
		if ($return) {
			return sprintf('/GS%d gs', $gs);
		} else {
			$this->_out(sprintf('/GS%d gs', $gs));
		}
	}

	function ResetStyles()
	{
		foreach (['B', 'I'] as $s) {
			$this->$s = false;
		}
		$this->currentfontstyle = '';
		$this->SetFont('', '', 0, false);
	}

	function _SetTextRendering($mode)
	{
		if (!(($mode == 0) || ($mode == 1) || ($mode == 2))) {
			throw new MpdfException("Text rendering mode should be 0, 1 or 2 (value : $mode)");
		}
		$tr = ($mode . ' Tr');
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']))) {
			$this->_out($tr);
		}
		$this->pageoutput[$this->page]['TextRendering'] = $tr;
	}

	function SetTextOutline($params = [])
	{
		if (isset($params['outline-s']) && $params['outline-s']) {
			$this->SetLineWidth($params['outline-WIDTH']);
			$this->SetDColor($params['outline-COLOR']);
			$tr = ('2 Tr');
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']))) {
				$this->_out($tr);
			}
			$this->pageoutput[$this->page]['TextRendering'] = $tr;
		} else { // Now resets all values
			$this->SetLineWidth(0.2);
			$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			$this->_SetTextRendering(0);
			$tr = ('0 Tr');
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']))) {
				$this->_out($tr);
			}
			$this->pageoutput[$this->page]['TextRendering'] = $tr;
		}
	}

	function RestrictUnicodeFonts($res)
	{
		// $res = array of (Unicode) fonts to restrict to: e.g. norasi|norasiB - language specific
		if (count($res)) { // Leave full list of available fonts if passed blank array
			$this->available_unifonts = $res;
		} else {
			$this->available_unifonts = $this->default_available_fonts;
		}
		if (count($this->available_unifonts) == 0) {
			$this->available_unifonts[] = $this->default_available_fonts[0];
		}
		$this->available_unifonts = array_values($this->available_unifonts);
	}

	function _getNormalLineheight($desc = false)
	{
		if (!$desc) {
			$desc = $this->CurrentFont['desc'];
		}
		if (!isset($desc['Leading'])) {
			$desc['Leading'] = 0;
		}
		if ($this->useFixedNormalLineHeight) {
			$lh = $this->normalLineheight;
		} elseif (isset($desc['Ascent']) && $desc['Ascent']) {
			$lh = ($this->adjustFontDescLineheight * ($desc['Ascent'] - $desc['Descent'] + $desc['Leading']) / 1000);
		} else {
			$lh = $this->normalLineheight;
		}
		return $lh;
	}

	function _computeLineheight($lh, $fs = '')
	{
		if ($this->shrin_k > 1) {
			$k = $this->shrin_k;
		} else {
			$k = 1;
		}
		if (!$fs) {
			$fs = $this->FontSize;
		}
		if ($lh == 'N') {
			$lh = $this->_getNormalLineheight();
		}
		if (preg_match('/mm/', $lh)) {
			return (((float) $lh) / $k); // convert to number
		} elseif ($lh > 0) {
			return ($fs * $lh);
		}
		return ($fs * $this->normalLineheight);
	}

	function SetLineHeight($FontPt = '', $lh = '')
	{
		if (!$FontPt) {
			$FontPt = $this->FontSizePt;
		}
		$fs = $FontPt / fpdf_protection::SCALE;
		$this->lineheight = $this->_computeLineheight($lh, $fs);
	}

	function SetDash($black = false, $white = false)
	{
		if ($black and $white) {
			$s = sprintf('[%.3F %.3F] 0 d', $black * fpdf_protection::SCALE, $white * fpdf_protection::SCALE);
		} else {
			$s = '[] 0 d';
		}
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Dash']) && $this->pageoutput[$this->page]['Dash'] != $s) || !isset($this->pageoutput[$this->page]['Dash']))) {
			$this->_out($s);
		}
		$this->pageoutput[$this->page]['Dash'] = $s;
	}

	function SetDefaultFontSize($fontsize)
	{
		$this->default_font_size = $fontsize;
		$this->original_default_font_size = $fontsize;
		$this->SetFontSize($fontsize);
		$this->defaultCSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
		$this->cssManager->CSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
	}

	function SetDefaultFont($font)
	{
		// Disallow embedded fonts to be used as defaults in PDFA
		if ($this->PDFA || $this->PDFX) {
			if (strtolower($font) == 'ctimes') {
				$font = 'serif';
			}
			if (strtolower($font) == 'ccourier') {
				$font = 'monospace';
			}
			if (strtolower($font) == 'chelvetica') {
				$font = 'sans-serif';
			}
		}
		$font = $this->SetFont($font); // returns substituted font if necessary
		$this->default_font = $font;
		$this->original_default_font = $font;
		if (!$this->watermark_font) {
			$this->watermark_font = $font;
		} // *WATERMARK*
		$this->defaultCSS['BODY']['FONT-FAMILY'] = $font;
		$this->cssManager->CSS['BODY']['FONT-FAMILY'] = $font;
	}

	function border_details($bd)
	{
		$prop = preg_split('/\s+/', trim($bd));

		if (isset($this->blk[$this->blklvl]['inner_width'])) {
			$refw = $this->blk[$this->blklvl]['inner_width'];
		} elseif (isset($this->blk[$this->blklvl - 1]['inner_width'])) {
			$refw = $this->blk[$this->blklvl - 1]['inner_width'];
		} else {
			$refw = $this->w;
		}
		if (count($prop) == 1) {
			$bsize = $this->sizeConverter->convert($prop[0], $refw, $this->FontSize, false);
			if ($bsize > 0) {
				return ['s' => 1, 'w' => $bsize, 'c' => $this->colorConverter->convert(0, $this->PDFAXwarnings), 'style' => 'solid'];
			} else {
				return ['w' => 0, 's' => 0];
			}
		} elseif (count($prop) == 2) {
			// 1px solid
			if (in_array($prop[1], $this->borderstyles) || $prop[1] == 'none' || $prop[1] == 'hidden') {
				$prop[2] = '';
			} // solid #000000
			elseif (in_array($prop[0], $this->borderstyles) || $prop[0] == 'none' || $prop[0] == 'hidden') {
				$prop[0] = '';
				$prop[1] = $prop[0];
				$prop[2] = $prop[1];
			} // 1px #000000
			else {
				$prop[1] = '';
				$prop[2] = $prop[1];
			}
		} elseif (count($prop) == 3) {
			// Change #000000 1px solid to 1px solid #000000 (proper)
			if (substr($prop[0], 0, 1) == '#') {
				$tmp = $prop[0];
				$prop[0] = $prop[1];
				$prop[1] = $prop[2];
				$prop[2] = $tmp;
			} // Change solid #000000 1px to 1px solid #000000 (proper)
			elseif (substr($prop[0], 1, 1) == '#') {
				$tmp = $prop[1];
				$prop[0] = $prop[2];
				$prop[1] = $prop[0];
				$prop[2] = $tmp;
			} // Change solid 1px #000000 to 1px solid #000000 (proper)
			elseif (in_array($prop[0], $this->borderstyles) || $prop[0] == 'none' || $prop[0] == 'hidden') {
				$tmp = $prop[0];
				$prop[0] = $prop[1];
				$prop[1] = $tmp;
			}
		} else {
			return [];
		}
		// Size
		$bsize = $this->sizeConverter->convert($prop[0], $refw, $this->FontSize, false);
		// color
		$coul = $this->colorConverter->convert($prop[2], $this->PDFAXwarnings); // returns array
		// Style
		$prop[1] = strtolower($prop[1]);
		if (in_array($prop[1], $this->borderstyles) && $bsize > 0) {
			$on = 1;
		} elseif ($prop[1] == 'hidden') {
			$on = 1;
			$bsize = 0;
			$coul = '';
		} elseif ($prop[1] == 'none') {
			$on = 0;
			$bsize = 0;
			$coul = '';
		} else {
			$on = 0;
			$bsize = 0;
			$coul = '';
			$prop[1] = '';
		}
		return ['s' => $on, 'w' => $bsize, 'c' => $coul, 'style' => $prop[1], 'dom' => 0];
	}

	function fixLineheight($v)
	{
		$lh = false;
		if (preg_match('/^[0-9\.,]*$/', $v) && $v >= 0) {
			return ($v + 0);
		} elseif (strtoupper($v) == 'NORMAL' || $v == 'N') {
			return 'N';  // mPDF 6
		} else {
			$tlh = $this->sizeConverter->convert($v, $this->FontSize, $this->FontSize, true);
			if ($tlh) {
				return ($tlh . 'mm');
			}
		}
		return $this->normalLineheight;
	}

	function setCSS($arrayaux, $type = '', $tag = '')
	{
	// type= INLINE | BLOCK | TABLECELL // tag= BODY
		if (!is_array($arrayaux)) {
			return; // Removes PHP Warning
		}

		// mPDF 5.7.3  inline text-decoration parameters
		$preceeding_fontkey = $this->FontFamily . $this->FontStyle;
		$preceeding_fontsize = $this->FontSize;
		$spanbordset = false;
		$spanbgset = false;
		// mPDF 6
		$prevlevel = (($this->blklvl == 0) ? 0 : $this->blklvl - 1);

		// Set font size first so that e.g. MARGIN 0.83em works on font size for this element
		if (isset($arrayaux['FONT-SIZE'])) {
			$v = $arrayaux['FONT-SIZE'];
			if (is_numeric($v[0])) {
				if ($type == 'BLOCK' && $this->blklvl > 0 && isset($this->blk[$this->blklvl - 1]['InlineProperties']) && isset($this->blk[$this->blklvl - 1]['InlineProperties']['size'])) {
					$mmsize = $this->sizeConverter->convert($v, $this->blk[$this->blklvl - 1]['InlineProperties']['size']);
				} elseif ($type == 'TABLECELL') {
					$mmsize = $this->sizeConverter->convert($v, $this->default_font_size / fpdf_protection::SCALE);
				} else {
					$mmsize = $this->sizeConverter->convert($v, $this->FontSize);
				}
				$this->SetFontSize($mmsize * (fpdf_protection::SCALE), false); // Get size in points (pt)
			} else {
				$v = strtoupper($v);
				if (isset($this->fontsizes[$v])) {
					$this->SetFontSize($this->fontsizes[$v] * $this->default_font_size, false);
				}
			}
			if ($tag == 'BODY') {
				$this->SetDefaultFontSize($this->FontSizePt);
			}
		}

		// mPDF 6
		if (isset($arrayaux['LANG']) && $arrayaux['LANG']) {
			if ($this->autoLangToFont && !$this->usingCoreFont) {
				if ($arrayaux['LANG'] != $this->default_lang && $arrayaux['LANG'] != 'UTF-8') {
					list ($coreSuitable, $mpdf_pdf_unifont) = $this->languageToFont->getLanguageOptions($arrayaux['LANG'], $this->useAdobeCJK);
					if ($mpdf_pdf_unifont) {
						$arrayaux['FONT-FAMILY'] = $mpdf_pdf_unifont;
					}
					if ($tag == 'BODY') {
						$this->default_lang = $arrayaux['LANG'];
					}
				}
			}
			$this->currentLang = $arrayaux['LANG'];
		}

		// FOR INLINE and BLOCK OR 'BODY'
		if (isset($arrayaux['FONT-FAMILY'])) {
			$v = $arrayaux['FONT-FAMILY'];
			// If it is a font list, get all font types
			$aux_fontlist = explode(",", $v);
			$found = 0;
			foreach ($aux_fontlist as $f) {
				$fonttype = trim($f);
				$fonttype = preg_replace('/["\']*(.*?)["\']*/', '\\1', $fonttype);
				$fonttype = preg_replace('/ /', '', $fonttype);
				$v = strtolower(trim($fonttype));
				if (isset($this->fonttrans[$v]) && $this->fonttrans[$v]) {
					$v = $this->fonttrans[$v];
				}
				if ((!$this->onlyCoreFonts && in_array($v, $this->available_unifonts)) ||
					in_array($v, ['ccourier', 'ctimes', 'chelvetica']) ||
					($this->onlyCoreFonts && in_array($v, ['courier', 'times', 'helvetica', 'arial'])) ||
					in_array($v, ['sjis', 'uhc', 'big5', 'gb'])) {
					$fonttype = $v;
					$found = 1;
					break;
				}
			}
			if (!$found) {
				foreach ($aux_fontlist as $f) {
					$fonttype = trim($f);
					$fonttype = preg_replace('/["\']*(.*?)["\']*/', '\\1', $fonttype);
					$fonttype = preg_replace('/ /', '', $fonttype);
					$v = strtolower(trim($fonttype));
					if (isset($this->fonttrans[$v]) && $this->fonttrans[$v]) {
						$v = $this->fonttrans[$v];
					}
					if (in_array($v, $this->sans_fonts) || in_array($v, $this->serif_fonts) || in_array($v, $this->mono_fonts)) {
						$fonttype = $v;
						break;
					}
				}
			}

			if ($tag == 'BODY') {
				$this->SetDefaultFont($fonttype);
			}
			$this->SetFont($fonttype, $this->currentfontstyle, 0, false);
		} else {
			$this->SetFont($this->currentfontfamily, $this->currentfontstyle, 0, false);
		}

		foreach ($arrayaux as $k => $v) {
			if ($type != 'INLINE' && $tag != 'BODY' && $type != 'TABLECELL') {
				switch ($k) {
					// BORDERS
					case 'BORDER-TOP':
						$this->blk[$this->blklvl]['border_top'] = $this->border_details($v);
						if ($this->blk[$this->blklvl]['border_top']['s']) {
							$this->blk[$this->blklvl]['border'] = 1;
						}
						break;
					case 'BORDER-BOTTOM':
						$this->blk[$this->blklvl]['border_bottom'] = $this->border_details($v);
						if ($this->blk[$this->blklvl]['border_bottom']['s']) {
							$this->blk[$this->blklvl]['border'] = 1;
						}
						break;
					case 'BORDER-LEFT':
						$this->blk[$this->blklvl]['border_left'] = $this->border_details($v);
						if ($this->blk[$this->blklvl]['border_left']['s']) {
							$this->blk[$this->blklvl]['border'] = 1;
						}
						break;
					case 'BORDER-RIGHT':
						$this->blk[$this->blklvl]['border_right'] = $this->border_details($v);
						if ($this->blk[$this->blklvl]['border_right']['s']) {
							$this->blk[$this->blklvl]['border'] = 1;
						}
						break;

					// PADDING
					case 'PADDING-TOP':
						$this->blk[$this->blklvl]['padding_top'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'PADDING-BOTTOM':
						$this->blk[$this->blklvl]['padding_bottom'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'PADDING-LEFT':
						if (($tag == 'UL' || $tag == 'OL') && $v == 'auto') {
							$this->blk[$this->blklvl]['padding_left'] = 'auto';
							break;
						}
						$this->blk[$this->blklvl]['padding_left'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'PADDING-RIGHT':
						if (($tag == 'UL' || $tag == 'OL') && $v == 'auto') {
							$this->blk[$this->blklvl]['padding_right'] = 'auto';
							break;
						}
						$this->blk[$this->blklvl]['padding_right'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;

					// MARGINS
					case 'MARGIN-TOP':
						$tmp = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						if (isset($this->blk[$this->blklvl]['lastbottommargin'])) {
							if ($tmp > $this->blk[$this->blklvl]['lastbottommargin']) {
								$tmp -= $this->blk[$this->blklvl]['lastbottommargin'];
							} else {
								$tmp = 0;
							}
						}
						$this->blk[$this->blklvl]['margin_top'] = $tmp;
						break;
					case 'MARGIN-BOTTOM':
						$this->blk[$this->blklvl]['margin_bottom'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'MARGIN-LEFT':
						$this->blk[$this->blklvl]['margin_left'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'MARGIN-RIGHT':
						$this->blk[$this->blklvl]['margin_right'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;

					/* -- BORDER-RADIUS -- */
					case 'BORDER-TOP-LEFT-RADIUS-H':
						$this->blk[$this->blklvl]['border_radius_TL_H'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'BORDER-TOP-LEFT-RADIUS-V':
						$this->blk[$this->blklvl]['border_radius_TL_V'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'BORDER-TOP-RIGHT-RADIUS-H':
						$this->blk[$this->blklvl]['border_radius_TR_H'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'BORDER-TOP-RIGHT-RADIUS-V':
						$this->blk[$this->blklvl]['border_radius_TR_V'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'BORDER-BOTTOM-LEFT-RADIUS-H':
						$this->blk[$this->blklvl]['border_radius_BL_H'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'BORDER-BOTTOM-LEFT-RADIUS-V':
						$this->blk[$this->blklvl]['border_radius_BL_V'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'BORDER-BOTTOM-RIGHT-RADIUS-H':
						$this->blk[$this->blklvl]['border_radius_BR_H'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					case 'BORDER-BOTTOM-RIGHT-RADIUS-V':
						$this->blk[$this->blklvl]['border_radius_BR_V'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						break;
					/* -- END BORDER-RADIUS -- */

					case 'BOX-SHADOW':
						$bs = $this->cssManager->setCSSboxshadow($v);
						if ($bs) {
							$this->blk[$this->blklvl]['box_shadow'] = $bs;
						}
						break;

					case 'BACKGROUND-CLIP':
						if (strtoupper($v) == 'PADDING-BOX') {
							$this->blk[$this->blklvl]['background_clip'] = 'padding-box';
						} elseif (strtoupper($v) == 'CONTENT-BOX') {
							$this->blk[$this->blklvl]['background_clip'] = 'content-box';
						}
						break;

					case 'PAGE-BREAK-AFTER':
						if (strtoupper($v) == 'AVOID') {
							$this->blk[$this->blklvl]['page_break_after_avoid'] = true;
						} elseif (strtoupper($v) == 'ALWAYS' || strtoupper($v) == 'LEFT' || strtoupper($v) == 'RIGHT') {
							$this->blk[$this->blklvl]['page_break_after'] = strtoupper($v);
						}
						break;

					// mPDF 6 pagebreaktype
					case 'BOX-DECORATION-BREAK':
						if (strtoupper($v) == 'CLONE') {
							$this->blk[$this->blklvl]['box_decoration_break'] = 'clone';
						} elseif (strtoupper($v) == 'SLICE') {
							$this->blk[$this->blklvl]['box_decoration_break'] = 'slice';
						}
						break;

					case 'WIDTH':
						if (strtoupper($v) != 'AUTO') {
							$this->blk[$this->blklvl]['css_set_width'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false);
						}
						break;

					// mPDF 6  Lists
					// LISTS
					case 'LIST-STYLE-TYPE':
						$this->blk[$this->blklvl]['list_style_type'] = strtolower($v);
						break;
					case 'LIST-STYLE-IMAGE':
						$this->blk[$this->blklvl]['list_style_image'] = strtolower($v);
						break;
					case 'LIST-STYLE-POSITION':
						$this->blk[$this->blklvl]['list_style_position'] = strtolower($v);
						break;
				}//end of switch($k)
			}


			if ($type != 'INLINE' && $type != 'TABLECELL') { // All block-level, including BODY tag
				switch ($k) {
					case 'TEXT-INDENT':
						// Computed value - to inherit
						$this->blk[$this->blklvl]['text_indent'] = $this->sizeConverter->convert($v, $this->blk[$prevlevel]['inner_width'], $this->FontSize, false) . 'mm';
						break;

					case 'MARGIN-COLLAPSE': // Custom tag to collapse margins at top and bottom of page
						if (strtoupper($v) == 'COLLAPSE') {
							$this->blk[$this->blklvl]['margin_collapse'] = true;
						}
						break;

					case 'LINE-HEIGHT':
						$this->blk[$this->blklvl]['line_height'] = $this->fixLineheight($v);
						if (!$this->blk[$this->blklvl]['line_height']) {
							$this->blk[$this->blklvl]['line_height'] = 'N';
						} // mPDF 6
						break;

					// mPDF 6
					case 'LINE-STACKING-STRATEGY':
						$this->blk[$this->blklvl]['line_stacking_strategy'] = strtolower($v);
						break;

					case 'LINE-STACKING-SHIFT':
						$this->blk[$this->blklvl]['line_stacking_shift'] = strtolower($v);
						break;

					case 'TEXT-ALIGN': // left right center justify
						switch (strtoupper($v)) {
							case 'LEFT':
								$this->blk[$this->blklvl]['align'] = "L";
								break;
							case 'CENTER':
								$this->blk[$this->blklvl]['align'] = "C";
								break;
							case 'RIGHT':
								$this->blk[$this->blklvl]['align'] = "R";
								break;
							case 'JUSTIFY':
								$this->blk[$this->blklvl]['align'] = "J";
								break;
						}
						break;

					/* -- BACKGROUNDS -- */
					case 'BACKGROUND-GRADIENT':
						if ($type == 'BLOCK') {
							$this->blk[$this->blklvl]['gradient'] = $v;
						}
						break;
					/* -- END BACKGROUNDS -- */

					case 'DIRECTION':
						if ($v) {
							$this->blk[$this->blklvl]['direction'] = strtolower($v);
						}
						break;
				}
			}

			// FOR INLINE ONLY
			if ($type == 'INLINE') {
				switch ($k) {
					case 'DISPLAY':
						if (strtoupper($v) == 'NONE') {
							$this->inlineDisplayOff = true;
						}
						break;
					case 'DIRECTION':
						break;
				}
			}
			// FOR INLINE ONLY
			if ($type == 'INLINE') {
				switch ($k) {
					// BORDERS
					case 'BORDER-TOP':
						$this->spanborddet['T'] = $this->border_details($v);
						$this->spanborder = true;
						$spanbordset = true;
						break;
					case 'BORDER-BOTTOM':
						$this->spanborddet['B'] = $this->border_details($v);
						$this->spanborder = true;
						$spanbordset = true;
						break;
					case 'BORDER-LEFT':
						$this->spanborddet['L'] = $this->border_details($v);
						$this->spanborder = true;
						$spanbordset = true;
						break;
					case 'BORDER-RIGHT':
						$this->spanborddet['R'] = $this->border_details($v);
						$this->spanborder = true;
						$spanbordset = true;
						break;
					case 'VISIBILITY': // block is set in OpenTag
						$v = strtolower($v);
						if ($v == 'visible' || $v == 'hidden' || $v == 'printonly' || $v == 'screenonly') {
							$this->textparam['visibility'] = $v;
						}
						break;
				}//end of switch($k)
			}

			if ($type != 'TABLECELL') {
				// FOR INLINE and BLOCK
				switch ($k) {
					case 'TEXT-ALIGN': // left right center justify
						if (strtoupper($v) == 'NOJUSTIFY' && $this->blk[$this->blklvl]['align'] == "J") {
							$this->blk[$this->blklvl]['align'] = "";
						}
						break;
					// bgcolor only - to stay consistent with original html2fpdf
					case 'BACKGROUND':
					case 'BACKGROUND-COLOR':
						$cor = $this->colorConverter->convert($v, $this->PDFAXwarnings);
						if ($cor) {
							if ($tag == 'BODY') {
								$this->bodyBackgroundColor = $cor;
							} elseif ($type == 'INLINE') {
								$this->spanbgcolorarray = $cor;
								$this->spanbgcolor = true;
								$spanbgset = true;
							} else {
								$this->blk[$this->blklvl]['bgcolorarray'] = $cor;
								$this->blk[$this->blklvl]['bgcolor'] = true;
							}
						} elseif ($type != 'INLINE') {
							if ($this->ColActive) {
								$this->blk[$this->blklvl]['bgcolorarray'] = $this->blk[$prevlevel]['bgcolorarray'];
								$this->blk[$this->blklvl]['bgcolor'] = $this->blk[$prevlevel]['bgcolor'];
							}
						}
						break;

					case 'VERTICAL-ALIGN': // super and sub only dealt with here e.g. <SUB> and <SUP>
						switch (strtoupper($v)) {
							case 'SUPER':
								$this->textvar = ($this->textvar | TextVars::FA_SUPERSCRIPT); // mPDF 5.7.1
								$this->textvar = ($this->textvar & ~TextVars::FA_SUBSCRIPT);
								// mPDF 5.7.3  inline text-decoration parameters
								if (isset($this->textparam['text-baseline'])) {
									$this->textparam['text-baseline'] += ($this->baselineSup) * $preceeding_fontsize;
								} else {
									$this->textparam['text-baseline'] = ($this->baselineSup) * $preceeding_fontsize;
								}
								break;
							case 'SUB':
								$this->textvar = ($this->textvar | TextVars::FA_SUBSCRIPT);
								$this->textvar = ($this->textvar & ~TextVars::FA_SUPERSCRIPT);
								// mPDF 5.7.3  inline text-decoration parameters
								if (isset($this->textparam['text-baseline'])) {
									$this->textparam['text-baseline'] += ($this->baselineSub) * $preceeding_fontsize;
								} else {
									$this->textparam['text-baseline'] = ($this->baselineSub) * $preceeding_fontsize;
								}
								break;
							case 'BASELINE':
								$this->textvar = ($this->textvar & ~TextVars::FA_SUBSCRIPT);
								$this->textvar = ($this->textvar & ~TextVars::FA_SUPERSCRIPT);
								// mPDF 5.7.3  inline text-decoration parameters
								if (isset($this->textparam['text-baseline'])) {
									unset($this->textparam['text-baseline']);
								}
								break;
							// mPDF 5.7.3  inline text-decoration parameters
							default:
								$lh = $this->_computeLineheight($this->blk[$this->blklvl]['line_height']);
								$sz = $this->sizeConverter->convert($v, $lh, $this->FontSize, false);
								$this->textvar = ($this->textvar & ~TextVars::FA_SUBSCRIPT);
								$this->textvar = ($this->textvar & ~TextVars::FA_SUPERSCRIPT);
								if ($sz) {
									if ($sz > 0) {
										$this->textvar = ($this->textvar | TextVars::FA_SUPERSCRIPT);
									} else {
										$this->textvar = ($this->textvar | TextVars::FA_SUBSCRIPT);
									}
									if (isset($this->textparam['text-baseline'])) {
										$this->textparam['text-baseline'] += $sz;
									} else {
										$this->textparam['text-baseline'] = $sz;
									}
								}
						}
						break;
				}//end of switch($k)
			}


			// FOR ALL
			switch ($k) {
				case 'LETTER-SPACING':
					$this->lSpacingCSS = $v;
					if (($this->lSpacingCSS || $this->lSpacingCSS === '0') && strtoupper($this->lSpacingCSS) != 'NORMAL') {
						$this->fixedlSpacing = $this->sizeConverter->convert($this->lSpacingCSS, $this->FontSize);
					}
					break;

				case 'WORD-SPACING':
					$this->wSpacingCSS = $v;
					if ($this->wSpacingCSS && strtoupper($this->wSpacingCSS) != 'NORMAL') {
						$this->minwSpacing = $this->sizeConverter->convert($this->wSpacingCSS, $this->FontSize);
					}
					break;

				case 'FONT-STYLE': // italic normal oblique
					switch (strtoupper($v)) {
						case 'ITALIC':
						case 'OBLIQUE':
							$this->SetStyle('I', true);
							break;
						case 'NORMAL':
							$this->SetStyle('I', false);
							break;
					}
					break;

				case 'FONT-WEIGHT': // normal bold // Does not support: bolder, lighter, 100..900(step value=100)
					switch (strtoupper($v)) {
						case 'BOLD':
							$this->SetStyle('B', true);
							break;
						case 'NORMAL':
							$this->SetStyle('B', false);
							break;
					}
					break;

				case 'FONT-KERNING':
					if (strtoupper($v) == 'NORMAL' || (strtoupper($v) == 'AUTO' && $this->useKerning)) {
						/* -- OTL -- */
						if ($this->CurrentFont['haskernGPOS']) {
							if (isset($this->OTLtags['Plus'])) {
								$this->OTLtags['Plus'] .= ' kern';
							} else {
								$this->OTLtags['Plus'] = ' kern';
							}
						} /* -- END OTL -- */ else {  // *OTL*
							$this->textvar = ($this->textvar | TextVars::FC_KERNING);
						} // *OTL*
					} elseif (strtoupper($v) == 'NONE' || (strtoupper($v) == 'AUTO' && !$this->useKerning)) {
						if (isset($this->OTLtags['Plus'])) {
							$this->OTLtags['Plus'] = str_replace('kern', '', $this->OTLtags['Plus']); // *OTL*
						}
						if (isset($this->OTLtags['FFPlus'])) {
							$this->OTLtags['FFPlus'] = preg_replace('/kern[\d]*/', '', $this->OTLtags['FFPlus']);
						}
						$this->textvar = ($this->textvar & ~TextVars::FC_KERNING);
					}
					break;

				/* -- OTL -- */
				case 'FONT-LANGUAGE-OVERRIDE':
					$v = strtoupper($v);
					if (strpos($v, 'NORMAL') !== false) {
						$this->fontLanguageOverride = '';
					} else {
						$this->fontLanguageOverride = trim($v);
					}
					break;


				case 'FONT-VARIANT-POSITION':
					if (isset($this->OTLtags['Plus'])) {
						$this->OTLtags['Plus'] = str_replace(['sups', 'subs'], '', $this->OTLtags['Plus']);
					}
					switch (strtoupper($v)) {
						case 'SUPER':
							$this->OTLtags['Plus'] .= ' sups';
							break;
						case 'SUB':
							$this->OTLtags['Plus'] .= ' subs';
							break;
						case 'NORMAL':
							break;
					}
					break;

				case 'FONT-VARIANT-CAPS':
					$v = strtoupper($v);
					if (!isset($this->OTLtags['Plus'])) {
						$this->OTLtags['Plus'] = '';
					}
					$this->OTLtags['Plus'] = str_replace(['c2sc', 'smcp', 'c2pc', 'pcap', 'unic', 'titl'], '', $this->OTLtags['Plus']);
					$this->textvar = ($this->textvar & ~TextVars::FC_SMALLCAPS);   // ?????????????? <small-caps>
					if (strpos($v, 'ALL-SMALL-CAPS') !== false) {
						$this->OTLtags['Plus'] .= ' c2sc smcp';
					} elseif (strpos($v, 'SMALL-CAPS') !== false) {
						if (isset($this->CurrentFont['hassmallcapsGSUB']) && $this->CurrentFont['hassmallcapsGSUB']) {
							$this->OTLtags['Plus'] .= ' smcp';
						} else {
							$this->textvar = ($this->textvar | TextVars::FC_SMALLCAPS);
						}
					} elseif (strpos($v, 'ALL-PETITE-CAPS') !== false) {
						$this->OTLtags['Plus'] .= ' c2pc pcap';
					} elseif (strpos($v, 'PETITE-CAPS') !== false) {
						$this->OTLtags['Plus'] .= ' pcap';
					} elseif (strpos($v, 'UNICASE') !== false) {
						$this->OTLtags['Plus'] .= ' unic';
					} elseif (strpos($v, 'TITLING-CAPS') !== false) {
						$this->OTLtags['Plus'] .= ' titl';
					}
					break;

				case 'FONT-VARIANT-LIGATURES':
					$v = strtoupper($v);
					if (!isset($this->OTLtags['Plus'])) {
						$this->OTLtags['Plus'] = '';
					}
					if (!isset($this->OTLtags['Minus'])) {
						$this->OTLtags['Minus'] = '';
					}
					if (strpos($v, 'NORMAL') !== false) {
						$this->OTLtags['Minus'] = str_replace(['liga', 'clig', 'calt'], '', $this->OTLtags['Minus']);
						$this->OTLtags['Plus'] = str_replace(['dlig', 'hlig'], '', $this->OTLtags['Plus']);
					} elseif (strpos($v, 'NONE') !== false) {
						$this->OTLtags['Minus'] .= ' liga clig calt';
						$this->OTLtags['Plus'] = str_replace(['dlig', 'hlig'], '', $this->OTLtags['Plus']);
					}
					if (strpos($v, 'NO-COMMON-LIGATURES') !== false) {
						$this->OTLtags['Minus'] .= ' liga clig';
					} elseif (strpos($v, 'COMMON-LIGATURES') !== false) {
						$this->OTLtags['Minus'] = str_replace(['liga', 'clig'], '', $this->OTLtags['Minus']);
					}
					if (strpos($v, 'NO-CONTEXTUAL') !== false) {
						$this->OTLtags['Minus'] .= ' calt';
					} elseif (strpos($v, 'CONTEXTUAL') !== false) {
						$this->OTLtags['Minus'] = str_replace('calt', '', $this->OTLtags['Minus']);
					}
					if (strpos($v, 'NO-DISCRETIONARY-LIGATURES') !== false) {
						$this->OTLtags['Plus'] = str_replace('dlig', '', $this->OTLtags['Plus']);
					} elseif (strpos($v, 'DISCRETIONARY-LIGATURES') !== false) {
						$this->OTLtags['Plus'] .= ' dlig';
					}
					if (strpos($v, 'NO-HISTORICAL-LIGATURES') !== false) {
						$this->OTLtags['Plus'] = str_replace('hlig', '', $this->OTLtags['Plus']);
					} elseif (strpos($v, 'HISTORICAL-LIGATURES') !== false) {
						$this->OTLtags['Plus'] .= ' hlig';
					}

					break;

				case 'FONT-VARIANT-NUMERIC':
					$v = strtoupper($v);
					if (!isset($this->OTLtags['Plus'])) {
						$this->OTLtags['Plus'] = '';
					}
					if (strpos($v, 'NORMAL') !== false) {
						$this->OTLtags['Plus'] = str_replace(['ordn', 'zero', 'lnum', 'onum', 'pnum', 'tnum', 'frac', 'afrc'], '', $this->OTLtags['Plus']);
					}
					if (strpos($v, 'ORDINAL') !== false) {
						$this->OTLtags['Plus'] .= ' ordn';
					}
					if (strpos($v, 'SLASHED-ZERO') !== false) {
						$this->OTLtags['Plus'] .= ' zero';
					}
					if (strpos($v, 'LINING-NUMS') !== false) {
						$this->OTLtags['Plus'] .= ' lnum';
						$this->OTLtags['Plus'] = str_replace('onum', '', $this->OTLtags['Plus']);
					} elseif (strpos($v, 'OLDSTYLE-NUMS') !== false) {
						$this->OTLtags['Plus'] .= ' onum';
						$this->OTLtags['Plus'] = str_replace('lnum', '', $this->OTLtags['Plus']);
					}
					if (strpos($v, 'PROPORTIONAL-NUMS') !== false) {
						$this->OTLtags['Plus'] .= ' pnum';
						$this->OTLtags['Plus'] = str_replace('tnum', '', $this->OTLtags['Plus']);
					} elseif (strpos($v, 'TABULAR-NUMS') !== false) {
						$this->OTLtags['Plus'] .= ' tnum';
						$this->OTLtags['Plus'] = str_replace('pnum', '', $this->OTLtags['Plus']);
					}
					if (strpos($v, 'DIAGONAL-FRACTIONS') !== false) {
						$this->OTLtags['Plus'] .= ' frac';
						$this->OTLtags['Plus'] = str_replace('afrc', '', $this->OTLtags['Plus']);
					} elseif (strpos($v, 'STACKED-FRACTIONS') !== false) {
						$this->OTLtags['Plus'] .= ' afrc';
						$this->OTLtags['Plus'] = str_replace('frac', '', $this->OTLtags['Plus']);
					}
					break;

				case 'FONT-VARIANT-ALTERNATES':  // Only supports historical-forms
					$v = strtoupper($v);
					if (!isset($this->OTLtags['Plus'])) {
						$this->OTLtags['Plus'] = '';
					}
					if (strpos($v, 'NORMAL') !== false) {
						$this->OTLtags['Plus'] = str_replace('hist', '', $this->OTLtags['Plus']);
					}
					if (strpos($v, 'HISTORICAL-FORMS') !== false) {
						$this->OTLtags['Plus'] .= ' hist';
					}
					break;


				case 'FONT-FEATURE-SETTINGS':
					$v = strtolower($v);
					if (strpos($v, 'normal') !== false) {
						$this->OTLtags['FFMinus'] = '';
						$this->OTLtags['FFPlus'] = '';
					} else {
						if (!isset($this->OTLtags['FFPlus'])) {
							$this->OTLtags['FFPlus'] = '';
						}
						if (!isset($this->OTLtags['FFMinus'])) {
							$this->OTLtags['FFMinus'] = '';
						}
						$tags = preg_split('/[,]/', $v);
						foreach ($tags as $t) {
							if (preg_match('/[\"\']([a-zA-Z0-9]{4})[\"\']\s*(on|off|\d*){0,1}/', $t, $m)) {
								if ($m[2] == 'off' || $m[2] === '0') {
									if (strpos($this->OTLtags['FFMinus'], $m[1]) === false) {
										$this->OTLtags['FFMinus'] .= ' ' . $m[1];
									}
									$this->OTLtags['FFPlus'] = preg_replace('/' . $m[1] . '[\d]*/', '', $this->OTLtags['FFPlus']);
								} else {
									if ($m[2] == 'on') {
										$m[2] = '1';
									}
									if (strpos($this->OTLtags['FFPlus'], $m[1]) === false) {
										$this->OTLtags['FFPlus'] .= ' ' . $m[1] . $m[2];
									}
									$this->OTLtags['FFMinus'] = str_replace($m[1], '', $this->OTLtags['FFMinus']);
								}
							}
						}
					}
					break;
				/* -- END OTL -- */


				case 'TEXT-TRANSFORM': // none uppercase lowercase // Does support: capitalize
					switch (strtoupper($v)) { // Not working 100%
						case 'CAPITALIZE':
							$this->textvar = ($this->textvar | TextVars::FT_CAPITALIZE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_UPPERCASE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_LOWERCASE); // mPDF 5.7.1
							break;
						case 'UPPERCASE':
							$this->textvar = ($this->textvar | TextVars::FT_UPPERCASE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_LOWERCASE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_CAPITALIZE); // mPDF 5.7.1
							break;
						case 'LOWERCASE':
							$this->textvar = ($this->textvar | TextVars::FT_LOWERCASE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_UPPERCASE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_CAPITALIZE); // mPDF 5.7.1
							break;
						case 'NONE':
							break;
							$this->textvar = ($this->textvar & ~TextVars::FT_UPPERCASE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_LOWERCASE); // mPDF 5.7.1
							$this->textvar = ($this->textvar & ~TextVars::FT_CAPITALIZE); // mPDF 5.7.1
					}
					break;

				case 'TEXT-SHADOW':
					$ts = $this->cssManager->setCSStextshadow($v);
					if ($ts) {
						$this->textshadow = $ts;
					}
					break;

				case 'HYPHENS':
					if (strtoupper($v) == 'NONE') {
						$this->textparam['hyphens'] = 2;
					} elseif (strtoupper($v) == 'AUTO') {
						$this->textparam['hyphens'] = 1;
					} elseif (strtoupper($v) == 'MANUAL') {
						$this->textparam['hyphens'] = 0;
					}
					break;

				case 'TEXT-OUTLINE':
					if (strtoupper($v) == 'NONE') {
						$this->textparam['outline-s'] = false;
					}
					break;

				case 'TEXT-OUTLINE-WIDTH':
				case 'OUTLINE-WIDTH':
					switch (strtoupper($v)) {
						case 'THIN':
							$v = '0.03em';
							break;
						case 'MEDIUM':
							$v = '0.05em';
							break;
						case 'THICK':
							$v = '0.07em';
							break;
					}
					$w = $this->sizeConverter->convert($v, $this->FontSize, $this->FontSize);
					if ($w) {
						$this->textparam['outline-WIDTH'] = $w;
						$this->textparam['outline-s'] = true;
					} else {
						$this->textparam['outline-s'] = false;
					}
					break;

				case 'TEXT-OUTLINE-COLOR':
				case 'OUTLINE-COLOR':
					if (strtoupper($v) == 'INVERT') {
						if ($this->colorarray) {
							$cor = $this->colorarray;
							$this->textparam['outline-COLOR'] = $this->colorConverter->invert($cor);
						} else {
							$this->textparam['outline-COLOR'] = $this->colorConverter->convert(255, $this->PDFAXwarnings);
						}
					} else {
						$cor = $this->colorConverter->convert($v, $this->PDFAXwarnings);
						if ($cor) {
							$this->textparam['outline-COLOR'] = $cor;
						}
					}
					break;

				case 'COLOR': // font color
					$cor = $this->colorConverter->convert($v, $this->PDFAXwarnings);
					if ($cor) {
						$this->colorarray = $cor;
						$this->SetTColor($cor);
					}
					break;
			}//end of switch($k)
		}//end of foreach
		// mPDF 5.7.3  inline text-decoration parameters
		// Needs to be set at the end - after vertical-align = super/sub, so that textparam['text-baseline'] is set
		if (isset($arrayaux['TEXT-DECORATION'])) {
			$v = $arrayaux['TEXT-DECORATION']; // none underline line-through (strikeout) // Does not support: blink
			if (stristr($v, 'LINE-THROUGH')) {
				$this->textvar = ($this->textvar | TextVars::FD_LINETHROUGH);
				// mPDF 5.7.3  inline text-decoration parameters
				if (isset($this->textparam['text-baseline'])) {
					$this->textparam['s-decoration']['baseline'] = $this->textparam['text-baseline'];
				} else {
					$this->textparam['s-decoration']['baseline'] = 0;
				}
				$this->textparam['s-decoration']['fontkey'] = $this->FontFamily . $this->FontStyle;
				$this->textparam['s-decoration']['fontsize'] = $this->FontSize;
				$this->textparam['s-decoration']['color'] = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			}
			if (stristr($v, 'UNDERLINE')) {
				$this->textvar = ($this->textvar | TextVars::FD_UNDERLINE);
				// mPDF 5.7.3  inline text-decoration parameters
				if (isset($this->textparam['text-baseline'])) {
					$this->textparam['u-decoration']['baseline'] = $this->textparam['text-baseline'];
				} else {
					$this->textparam['u-decoration']['baseline'] = 0;
				}
				$this->textparam['u-decoration']['fontkey'] = $this->FontFamily . $this->FontStyle;
				$this->textparam['u-decoration']['fontsize'] = $this->FontSize;
				$this->textparam['u-decoration']['color'] = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			}
			if (stristr($v, 'OVERLINE')) {
				$this->textvar = ($this->textvar | TextVars::FD_OVERLINE);
				// mPDF 5.7.3  inline text-decoration parameters
				if (isset($this->textparam['text-baseline'])) {
					$this->textparam['o-decoration']['baseline'] = $this->textparam['text-baseline'];
				} else {
					$this->textparam['o-decoration']['baseline'] = 0;
				}
				$this->textparam['o-decoration']['fontkey'] = $this->FontFamily . $this->FontStyle;
				$this->textparam['o-decoration']['fontsize'] = $this->FontSize;
				$this->textparam['o-decoration']['color'] = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			}
			if (stristr($v, 'NONE')) {
				$this->textvar = ($this->textvar & ~TextVars::FD_UNDERLINE);
				$this->textvar = ($this->textvar & ~TextVars::FD_LINETHROUGH);
				$this->textvar = ($this->textvar & ~TextVars::FD_OVERLINE);
				// mPDF 5.7.3  inline text-decoration parameters
				if (isset($this->textparam['u-decoration'])) {
					unset($this->textparam['u-decoration']);
				}
				if (isset($this->textparam['s-decoration'])) {
					unset($this->textparam['s-decoration']);
				}
				if (isset($this->textparam['o-decoration'])) {
					unset($this->textparam['o-decoration']);
				}
			}
		}
		// mPDF 6
		if ($spanbordset) { // BORDER has been set on this INLINE element
			if (isset($this->textparam['text-baseline'])) {
				$this->textparam['bord-decoration']['baseline'] = $this->textparam['text-baseline'];
			} else {
				$this->textparam['bord-decoration']['baseline'] = 0;
			}
			$this->textparam['bord-decoration']['fontkey'] = $this->FontFamily . $this->FontStyle;
			$this->textparam['bord-decoration']['fontsize'] = $this->FontSize;
		}
		if ($spanbgset) { // BACKGROUND[-COLOR] has been set on this INLINE element
			if (isset($this->textparam['text-baseline'])) {
				$this->textparam['bg-decoration']['baseline'] = $this->textparam['text-baseline'];
			} else {
				$this->textparam['bg-decoration']['baseline'] = 0;
			}
			$this->textparam['bg-decoration']['fontkey'] = $this->FontFamily . $this->FontStyle;
			$this->textparam['bg-decoration']['fontsize'] = $this->FontSize;
		}
	}

	function Reset()
	{
		$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
		$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
		$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
		$this->SetAlpha(1);
		$this->colorarray = '';

		$this->spanbgcolorarray = '';
		$this->spanbgcolor = false;
		$this->spanborder = false;
		$this->spanborddet = [];

		$this->ResetStyles();

		$this->HREF = '';
		$this->textparam = [];
		$this->SetTextOutline();

		$this->textvar = 0x00; // mPDF 5.7.1
		$this->OTLtags = [];
		$this->textshadow = '';

		$this->currentLang = $this->default_lang;  // mPDF 6
		$this->RestrictUnicodeFonts($this->default_available_fonts); // mPDF 6
		$this->SetFont($this->default_font, '', 0, false);
		$this->SetFontSize($this->default_font_size, false);

		$this->currentfontfamily = '';
		$this->currentfontsize = '';
		$this->currentfontstyle = '';

		/* -- TABLES -- */
		if ($this->tableLevel && isset($this->table[1][1]['cellLineHeight'])) {
			$this->SetLineHeight('', $this->table[1][1]['cellLineHeight']); // *TABLES*
		} else { 		/* -- END TABLES -- */
			if (isset($this->blk[$this->blklvl]['line_height']) && $this->blk[$this->blklvl]['line_height']) {
				$this->SetLineHeight('', $this->blk[$this->blklvl]['line_height']); // sets default line height
			}
		}

		$this->lSpacingCSS = '';
		$this->wSpacingCSS = '';
		$this->fixedlSpacing = false;
		$this->minwSpacing = 0;
		$this->SetDash(); // restore to no dash
		$this->dash_on = false;
		$this->dotted_on = false;
		$this->divwidth = 0;
		$this->divheight = 0;
		$this->cellTextAlign = '';
		$this->cellLineHeight = '';
		$this->cellLineStackingStrategy = '';
		$this->cellLineStackingShift = '';
		$this->oldy = -1;

		$bodystyle = [];
		if (isset($this->cssManager->CSS['BODY']['FONT-STYLE'])) {
			$bodystyle['FONT-STYLE'] = $this->cssManager->CSS['BODY']['FONT-STYLE'];
		}
		if (isset($this->cssManager->CSS['BODY']['FONT-WEIGHT'])) {
			$bodystyle['FONT-WEIGHT'] = $this->cssManager->CSS['BODY']['FONT-WEIGHT'];
		}
		if (isset($this->cssManager->CSS['BODY']['COLOR'])) {
			$bodystyle['COLOR'] = $this->cssManager->CSS['BODY']['COLOR'];
		}
		if (isset($bodystyle)) {
			$this->setCSS($bodystyle, 'BLOCK', 'BODY');
		}
	}

	function _getStyledNumber($ppgno, $type, $listmarker = false)
	{
		if ($listmarker) {
			$reverse = true; // Reverse RTL numerals (Hebrew) when using for list
			$checkfont = true; // Using list - font is set, so check if character is available
		} else {
			$reverse = false; // For pagenumbers, RTL numerals (Hebrew) will get reversed later by bidi
			$checkfont = false; // For pagenumbers - font is not set, so no check
		}

		$decToAlpha = new Conversion\DecToAlpha();
		$decToCjk = new Conversion\DecToCjk();
		$decToHebrew = new Conversion\DecToHebrew();
		$decToRoman = new Conversion\DecToRoman();
		$decToOther = new Conversion\DecToOther($this);

		$lowertype = strtolower($type);

		if ($lowertype == 'upper-latin' || $lowertype == 'upper-alpha' || $type == 'A') {

			$ppgno = $decToAlpha->convert($ppgno, true);

		} elseif ($lowertype == 'lower-latin' || $lowertype == 'lower-alpha' || $type == 'a') {

			$ppgno = $decToAlpha->convert($ppgno, false);

		} elseif ($lowertype == 'upper-roman' || $type == 'I') {

			$ppgno = $decToRoman->convert($ppgno, true);

		} elseif ($lowertype == 'lower-roman' || $type == 'i') {

			$ppgno = $decToRoman->convert($ppgno, false);

		} elseif ($lowertype == 'hebrew') {

			$ppgno = $decToHebrew->convert($ppgno, $reverse);

		} elseif (preg_match('/(arabic-indic|bengali|devanagari|gujarati|gurmukhi|kannada|malayalam|oriya|persian|tamil|telugu|thai|urdu|cambodian|khmer|lao)/i', $lowertype, $m)) {

			$cp = $decToOther->getCodePage($m[1]);
			$ppgno = $decToOther->convert($ppgno, $cp, $checkfont);

		} elseif ($lowertype == 'cjk-decimal') {

			$ppgno = $decToCjk->convert($ppgno);

		}

		return $ppgno;
	}

	function docPageNum($num = 0, $extras = false)
	{
		if ($num < 1) {
			$num = $this->page;
		}

		$type = $this->defaultPageNumStyle; // set default Page Number Style
		$ppgno = $num;
		$suppress = 0;
		$offset = 0;
		$lastreset = 0;

		foreach ($this->PageNumSubstitutions as $psarr) {
			if ($num >= $psarr['from']) {
				if ($psarr['reset']) {
					if ($psarr['reset'] > 1) {
						$offset = $psarr['reset'] - 1;
					}
					$ppgno = $num - $psarr['from'] + 1 + $offset;
					$lastreset = $psarr['from'];
				}
				if ($psarr['type']) {
					$type = $psarr['type'];
				}
				if (strtoupper($psarr['suppress']) == 'ON' || $psarr['suppress'] == 1) {
					$suppress = 1;
				} elseif (strtoupper($psarr['suppress']) == 'OFF') {
					$suppress = 0;
				}
			}
		}

		if ($suppress) {
			return '';
		}

		$ppgno = $this->_getStyledNumber($ppgno, $type);

		if ($extras) {
			$ppgno = $this->pagenumPrefix . $ppgno . $this->pagenumSuffix;
		}

		return $ppgno;
	}

	function docPageNumTotal($num = 0, $extras = false)
	{
		if ($num < 1) {
			$num = $this->page;
		}

		$type = $this->defaultPageNumStyle; // set default Page Number Style
		$ppgstart = 1;
		$ppgend = count($this->pages) + 1;
		$suppress = 0;
		$offset = 0;

		foreach ($this->PageNumSubstitutions as $psarr) {
			if ($num >= $psarr['from']) {
				if ($psarr['reset']) {
					if ($psarr['reset'] > 1) {
						$offset = $psarr['reset'] - 1;
					}
					$ppgstart = $psarr['from'] + $offset;
					$ppgend = count($this->pages) + 1 + $offset;
				}
				if ($psarr['type']) {
					$type = $psarr['type'];
				}
				if (strtoupper($psarr['suppress']) == 'ON' || $psarr['suppress'] == 1) {
					$suppress = 1;
				} elseif (strtoupper($psarr['suppress']) == 'OFF') {
					$suppress = 0;
				}
			}
			if ($num < $psarr['from']) {
				if ($psarr['reset']) {
					$ppgend = $psarr['from'] + $offset;
					break;
				}
			}
		}

		if ($suppress) {
			return '';
		}

		$ppgno = $ppgend - $ppgstart + $offset;
		$ppgno = $this->_getStyledNumber($ppgno, $type);

		if ($extras) {
			$ppgno = $this->pagenumPrefix . $ppgno . $this->pagenumSuffix;
		}

		return $ppgno;
	}

	function ReadCharset($html)
	{
		// Charset conversion
		if ($this->allow_charset_conversion) {
			if (preg_match('/<head.*charset=([^\'\"\s]*).*<\/head>/si', $html, $m)) {
				if (strtoupper($m[1]) != 'UTF-8') {
					$this->charset_in = strtoupper($m[1]);
				}
			}
		}
	}

	function is_utf8(&$string)
	{
		if ($string === mb_convert_encoding(mb_convert_encoding($string, "UTF-32", "UTF-8"), "UTF-8", "UTF-32")) {
			return true;
		} else {
			if ($this->ignore_invalid_utf8) {
				$string = mb_convert_encoding(mb_convert_encoding($string, "UTF-32", "UTF-8"), "UTF-8", "UTF-32");
				return true;
			} else {
				return false;
			}
		}
	}

	function SubstituteHiEntities($html)
	{
		// converts html_entities > ASCII 127 to unicode
		// Leaves in particular &lt; to distinguish from tag marker
		if (count($this->entsearch)) {
			$html = str_replace($this->entsearch, $this->entsubstitute, $html);
		}
		return $html;
	}

	function purify_utf8($html, $lo = true)
	{
		// For HTML
		// Checks string is valid UTF-8 encoded
		// converts html_entities > ASCII 127 to UTF-8
		// Only exception - leaves low ASCII entities e.g. &lt; &amp; etc.
		// Leaves in particular &lt; to distinguish from tag marker
		if (!$this->is_utf8($html)) {
			while (mb_convert_encoding(mb_convert_encoding($html, "UTF-32", "UTF-8"), "UTF-8", "UTF-32") != $html) {
				$a = iconv('UTF-8', 'UTF-8', $html);
				// echo ($a);
				$pos = $start = strlen($a);
				$err = '';
				while (ord(substr($html, $pos, 1)) > 128) {
					$err .= '[[#' . ord(substr($html, $pos, 1)) . ']]';
					$pos++;
				}
				$this->logger->error($err, ['context' => LogContext::UTF8]);
				$html = substr($html, $pos);
			}
			throw new \Mpdf\MpdfException("HTML contains invalid UTF-8 character(s). See log for further details");
		}
		$html = preg_replace("/\r/", "", $html);

		// converts html_entities > ASCII 127 to UTF-8
		// Leaves in particular &lt; to distinguish from tag marker
		$html = $this->SubstituteHiEntities($html);

		// converts all &#nnn; or &#xHHH; to UTF-8 multibyte
		// If $lo==true then includes ASCII < 128
		$html = UtfString::strcode2utf($html, $lo);
		return ($html);
	}

	function initialiseBlock(&$blk)
	{
		$blk['margin_top'] = 0;
		$blk['margin_left'] = 0;
		$blk['margin_bottom'] = 0;
		$blk['margin_right'] = 0;
		$blk['padding_top'] = 0;
		$blk['padding_left'] = 0;
		$blk['padding_bottom'] = 0;
		$blk['padding_right'] = 0;
		$blk['border_top']['w'] = 0;
		$blk['border_left']['w'] = 0;
		$blk['border_bottom']['w'] = 0;
		$blk['border_right']['w'] = 0;
		$blk['direction'] = 'ltr';
		$blk['hide'] = false;
		$blk['outer_left_margin'] = 0;
		$blk['outer_right_margin'] = 0;
		$blk['cascadeCSS'] = [];
		$blk['block-align'] = false;
		$blk['bgcolor'] = false;
		$blk['page_break_after_avoid'] = false;
		$blk['keep_block_together'] = false;
		$blk['float'] = false;
		$blk['line_height'] = '';
		$blk['margin_collapse'] = false;
	}

	function ReadMetaTags($html)
	{
		// changes anykey=anyvalue to anykey="anyvalue" (only do this when this happens inside tags)
		$regexp = '/ (\\w+?)=([^\\s>"]+)/si';
		$html = preg_replace($regexp, " \$1=\"\$2\"", $html);
		if (preg_match('/<title>(.*?)<\/title>/si', $html, $m)) {
			$this->SetTitle($m[1]);
		}
		preg_match_all('/<meta [^>]*?(name|content)="([^>]*?)" [^>]*?(name|content)="([^>]*?)".*?>/si', $html, $aux);
		$firstattr = $aux[1];
		$secondattr = $aux[3];
		for ($i = 0; $i < count($aux[0]); $i++) {
			$name = ( strtoupper($firstattr[$i]) == "NAME" ) ? strtoupper($aux[2][$i]) : strtoupper($aux[4][$i]);
			$content = ( strtoupper($firstattr[$i]) == "CONTENT" ) ? $aux[2][$i] : $aux[4][$i];
			switch ($name) {
				case "KEYWORDS":
					$this->SetKeywords($content);
					break;
				case "AUTHOR":
					$this->SetAuthor($content);
					break;
				case "DESCRIPTION":
					$this->SetSubject($content);
					break;
			}
		}
	}

	function SetBasePath($str = '')
	{
		if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		} elseif (isset($_SERVER['SERVER_NAME'])) {
			$host = $_SERVER['SERVER_NAME'];
		} else {
			$host = '';
		}
		if (!$str) {
			if ($_SERVER['SCRIPT_NAME']) {
				$currentPath = dirname($_SERVER['SCRIPT_NAME']);
			} else {
				$currentPath = dirname($_SERVER['PHP_SELF']);
			}
			$currentPath = str_replace("\\", "/", $currentPath);
			if ($currentPath == '/') {
				$currentPath = '';
			}
			if ($host) {  // mPDF 6
				if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off') {
					$currpath = 'https://' . $host . $currentPath . '/';
				} else {
					$currpath = 'http://' . $host . $currentPath . '/';
				}
			} else {
				$currpath = '';
			}
			$this->basepath = $currpath;
			$this->basepathIsLocal = true;
			return;
		}
		$str = preg_replace('/\?.*/', '', $str);
		if (!preg_match('/(http|https|ftp):\/\/.*\//i', $str)) {
			$str .= '/';
		}
		$str .= 'xxx'; // in case $str ends in / e.g. http://www.bbc.co.uk/
		$this->basepath = dirname($str) . "/"; // returns e.g. e.g. http://www.google.com/dir1/dir2/dir3/
		$this->basepath = str_replace("\\", "/", $this->basepath); // If on Windows
		$tr = parse_url($this->basepath);
		if (isset($tr['host']) && ($tr['host'] == $host)) {
			$this->basepathIsLocal = true;
		} else {
			$this->basepathIsLocal = false;
		}
	}

	function SetDirectionality($dir = 'ltr')
	{
		/* -- OTL -- */
		if (strtolower($dir) == 'rtl') {
			if ($this->directionality != 'rtl') {
				// Swop L/R Margins so page 1 RTL is an 'even' page
				$tmp = $this->DeflMargin;
				$this->DeflMargin = $this->DefrMargin;
				$this->DefrMargin = $tmp;
				$this->orig_lMargin = $this->DeflMargin;
				$this->orig_rMargin = $this->DefrMargin;

				$this->SetMargins($this->DeflMargin, $this->DefrMargin, $this->tMargin);
			}
			$this->directionality = 'rtl';
			$this->defaultAlign = 'R';
			$this->defaultTableAlign = 'R';
		} else {
			/* -- END OTL -- */
			$this->directionality = 'ltr';
			$this->defaultAlign = 'L';
			$this->defaultTableAlign = 'L';
		} // *OTL*
		$this->cssManager->CSS['BODY']['DIRECTION'] = $this->directionality;
	}

	function saveInlineProperties()
	{
		$saved = [];
		$saved['family'] = $this->FontFamily;
		$saved['style'] = $this->FontStyle;
		$saved['sizePt'] = $this->FontSizePt;
		$saved['size'] = $this->FontSize;
		$saved['HREF'] = $this->HREF;
		$saved['textvar'] = $this->textvar; // mPDF 5.7.1
		$saved['OTLtags'] = $this->OTLtags; // mPDF 5.7.1
		$saved['textshadow'] = $this->textshadow;
		$saved['linewidth'] = $this->LineWidth;
		$saved['drawcolor'] = $this->DrawColor;
		$saved['textparam'] = $this->textparam;
		$saved['lSpacingCSS'] = $this->lSpacingCSS;
		$saved['wSpacingCSS'] = $this->wSpacingCSS;
		$saved['I'] = $this->I;
		$saved['B'] = $this->B;
		$saved['colorarray'] = $this->colorarray;
		$saved['bgcolorarray'] = $this->spanbgcolorarray;
		$saved['border'] = $this->spanborddet;
		$saved['color'] = $this->TextColor;
		$saved['bgcolor'] = $this->FillColor;
		$saved['lang'] = $this->currentLang;
		$saved['fontLanguageOverride'] = $this->fontLanguageOverride; // mPDF 5.7.1
		$saved['display_off'] = $this->inlineDisplayOff;

		return $saved;
	}

	function SetBackground(&$properties, &$maxwidth)
	{
		if (isset($properties['BACKGROUND-ORIGIN']) && ($properties['BACKGROUND-ORIGIN'] == 'border-box' || $properties['BACKGROUND-ORIGIN'] == 'content-box')) {
			$origin = $properties['BACKGROUND-ORIGIN'];
		} else {
			$origin = 'padding-box';
		}

		if (isset($properties['BACKGROUND-SIZE'])) {
			if (stristr($properties['BACKGROUND-SIZE'], 'contain')) {
				$bsw = $bsh = 'contain';
			} elseif (stristr($properties['BACKGROUND-SIZE'], 'cover')) {
				$bsw = $bsh = 'cover';
			} else {
				$bsw = $bsh = 'auto';
				$sz = preg_split('/\s+/', trim($properties['BACKGROUND-SIZE']));
				if (count($sz) == 2) {
					$bsw = $sz[0];
					$bsh = $sz[1];
				} else {
					$bsw = $sz[0];
				}
				if (!stristr($bsw, '%') && !stristr($bsw, 'auto')) {
					$bsw = $this->sizeConverter->convert($bsw, $maxwidth, $this->FontSize);
				}
				if (!stristr($bsh, '%') && !stristr($bsh, 'auto')) {
					$bsh = $this->sizeConverter->convert($bsh, $maxwidth, $this->FontSize);
				}
			}
			$size = ['w' => $bsw, 'h' => $bsh];
		} else {
			$size = false;
		} // mPDF 6
		if (preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $properties['BACKGROUND-IMAGE'])) {
			return ['gradient' => $properties['BACKGROUND-IMAGE'], 'origin' => $origin, 'size' => $size];
		} else {
			$file = $properties['BACKGROUND-IMAGE'];
			$sizesarray = $this->Image($file, 0, 0, 0, 0, '', '', false, false, false, false, true);
			if (isset($sizesarray['IMAGE_ID'])) {
				$image_id = $sizesarray['IMAGE_ID'];
				$orig_w = $sizesarray['WIDTH'] * fpdf_protection::SCALE;  // in user units i.e. mm
				$orig_h = $sizesarray['HEIGHT'] * fpdf_protection::SCALE;  // (using $this->img_dpi)
				if (isset($properties['BACKGROUND-IMAGE-RESOLUTION'])) {
					if (preg_match('/from-image/i', $properties['BACKGROUND-IMAGE-RESOLUTION']) && isset($sizesarray['set-dpi']) && $sizesarray['set-dpi'] > 0) {
						$orig_w *= $this->img_dpi / $sizesarray['set-dpi'];
						$orig_h *= $this->img_dpi / $sizesarray['set-dpi'];
					} elseif (preg_match('/(\d+)dpi/i', $properties['BACKGROUND-IMAGE-RESOLUTION'], $m)) {
						$dpi = $m[1];
						if ($dpi > 0) {
							$orig_w *= $this->img_dpi / $dpi;
							$orig_h *= $this->img_dpi / $dpi;
						}
					}
				}
				$x_repeat = true;
				$y_repeat = true;
				if (isset($properties['BACKGROUND-REPEAT'])) {
					if ($properties['BACKGROUND-REPEAT'] == 'no-repeat' || $properties['BACKGROUND-REPEAT'] == 'repeat-x') {
						$y_repeat = false;
					}
					if ($properties['BACKGROUND-REPEAT'] == 'no-repeat' || $properties['BACKGROUND-REPEAT'] == 'repeat-y') {
						$x_repeat = false;
					}
				}
				$x_pos = 0;
				$y_pos = 0;
				if (isset($properties['BACKGROUND-POSITION'])) {
					$ppos = preg_split('/\s+/', $properties['BACKGROUND-POSITION']);
					$x_pos = $ppos[0];
					$y_pos = $ppos[1];
					if (!stristr($x_pos, '%')) {
						$x_pos = $this->sizeConverter->convert($x_pos, $maxwidth, $this->FontSize);
					}
					if (!stristr($y_pos, '%')) {
						$y_pos = $this->sizeConverter->convert($y_pos, $maxwidth, $this->FontSize);
					}
				}
				if (isset($properties['BACKGROUND-IMAGE-RESIZE'])) {
					$resize = $properties['BACKGROUND-IMAGE-RESIZE'];
				} else {
					$resize = 0;
				}
				if (isset($properties['BACKGROUND-IMAGE-OPACITY'])) {
					$opacity = $properties['BACKGROUND-IMAGE-OPACITY'];
				} else {
					$opacity = 1;
				}
				return ['image_id' => $image_id, 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $x_pos, 'y_pos' => $y_pos, 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'resize' => $resize, 'opacity' => $opacity, 'itype' => $sizesarray['itype'], 'origin' => $origin, 'size' => $size];
			}
		}
		return false;
	}

	function GetFloatDivInfo($blklvl = 0, $clear = false)
	{
		// If blklvl specified, only returns floats at that level - for ClearFloats
		$l_exists = false;
		$r_exists = false;
		$l_max = 0;
		$r_max = 0;
		$l_width = 0;
		$r_width = 0;
		if (count($this->floatDivs)) {
			$currpos = ($this->page * 1000 + $this->y);
			foreach ($this->floatDivs as $f) {
				if (($clear && $f['blockContext'] == $this->blk[$blklvl]['blockContext']) || (!$clear && $currpos >= $f['startpos'] && $currpos < ($f['endpos'] - 0.001) && $f['blklvl'] > $blklvl && $f['blockContext'] == $this->blk[$blklvl]['blockContext'])) {
					if ($f['side'] == 'L') {
						$l_exists = true;
						$l_max = max($l_max, $f['endpos']);
						$l_width = max($l_width, $f['w']);
					}
					if ($f['side'] == 'R') {
						$r_exists = true;
						$r_max = max($r_max, $f['endpos']);
						$r_width = max($r_width, $f['w']);
					}
				}
			}
		}
		return [$l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width];
	}

	function UTF8StringToArray($str, $addSubset = true)
	{
		$out = [];
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++) {
			$uni = -1;
			$h = ord($str[$i]);
			if ($h <= 0x7F) {
				$uni = $h;
			} elseif ($h >= 0xC2) {
				if (($h <= 0xDF) && ($i < $len - 1)) {
					$uni = ($h & 0x1F) << 6 | (ord($str[++$i]) & 0x3F);
				} elseif (($h <= 0xEF) && ($i < $len - 2)) {
					$uni = ($h & 0x0F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
				} elseif (($h <= 0xF4) && ($i < $len - 3)) {
					$uni = ($h & 0x0F) << 18 | (ord($str[++$i]) & 0x3F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
				}
			}
			if ($uni >= 0) {
				$out[] = $uni;
				if ($addSubset && isset($this->CurrentFont['subset'])) {
					$this->CurrentFont['subset'][$uni] = $uni;
				}
			}
		}
		return $out;
	}

	function _getCharWidth(&$cw, $u, $isdef = true)
	{
		$w = 0;

		if ($u == 0) {
			$w = false;
		} elseif (isset($cw[$u * 2 + 1])) {
			$w = (ord($cw[$u * 2]) << 8) + ord($cw[$u * 2 + 1]);
		}

		if ($w == 65535) {
			return 0;
		} elseif ($w) {
			return $w;
		} elseif ($isdef) {
			return false;
		} else {
			return 0;
		}
	}

	function GetCharWidthNonCore($c, $addSubset = true)
	{
		// Get width of a single character in the current Non-Core font
		$c = (string) $c;
		$w = 0;
		$unicode = $this->UTF8StringToArray($c, $addSubset);
		$char = $unicode[0];
		/* -- CJK-FONTS -- */
		if ($this->CurrentFont['type'] == 'Type0') { // CJK Adobe fonts
			if ($char == 173) {
				return 0;
			} // Soft Hyphens
			elseif (isset($this->CurrentFont['cw'][$char])) {
				$w+=$this->CurrentFont['cw'][$char];
			} elseif (isset($this->CurrentFont['MissingWidth'])) {
				$w += $this->CurrentFont['MissingWidth'];
			} else {
				$w += 500;
			}
		} else {
			/* -- END CJK-FONTS -- */
			if ($char == 173) {
				return 0;
			} // Soft Hyphens
			elseif (($this->textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[$char])) { // mPDF 5.7.1
				$charw = $this->_getCharWidth($this->CurrentFont['cw'], $this->upperCase[$char]);
				if ($charw !== false) {
					$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
					$w+=$charw;
				} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$w += $this->CurrentFont['desc']['MissingWidth'];
				} elseif (isset($this->CurrentFont['MissingWidth'])) {
					$w += $this->CurrentFont['MissingWidth'];
				} else {
					$w += 500;
				}
			} else {
				$charw = $this->_getCharWidth($this->CurrentFont['cw'], $char);
				if ($charw !== false) {
					$w+=$charw;
				} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$w += $this->CurrentFont['desc']['MissingWidth'];
				} elseif (isset($this->CurrentFont['MissingWidth'])) {
					$w += $this->CurrentFont['MissingWidth'];
				} else {
					$w += 500;
				}
			}
		} // *CJK-FONTS*
		$w *= ($this->FontSize / 1000);
		if ($this->minwSpacing || $this->fixedlSpacing) {
			if ($c == ' ') {
				$nb_spaces = 1;
			} else {
				$nb_spaces = 0;
			}
			$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
		}
		return ($w);
	}

	function GetCharWidthCore($c)
	{
		// Get width of a single character in the current Core font
		$c = (string) $c;
		$w = 0;
		// Soft Hyphens chr(173)
		if ($c == chr(173) && $this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
			return 0;
		} elseif (($this->textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[ord($c)])) {  // mPDF 5.7.1
			$charw = $this->CurrentFont['cw'][chr($this->upperCase[ord($c)])];
			if ($charw !== false) {
				$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
				$w+=$charw;
			}
		} elseif (isset($this->CurrentFont['cw'][$c])) {
			$w += $this->CurrentFont['cw'][$c];
		} elseif (isset($this->CurrentFont['cw'][ord($c)])) {
			$w += $this->CurrentFont['cw'][ord($c)];
		}
		$w *= ($this->FontSize / 1000);
		if ($this->minwSpacing || $this->fixedlSpacing) {
			if ($c == ' ') {
				$nb_spaces = 1;
			} else {
				$nb_spaces = 0;
			}
			$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
		}
		return ($w);
	}

	function GetCharWidth($c, $addSubset = true)
	{
		if (!$this->usingCoreFont) {
			return $this->GetCharWidthNonCore($c, $addSubset);
		} else {
			return $this->GetCharWidthCore($c);
		}
	}

	function _resizeBackgroundImage($imw, $imh, $cw, $ch, $resize, $repx, $repy, $pba = [], $size = [])
	{
		// pba is background positioning area (from CSS background-origin) may not always be set [x,y,w,h]
		// size is from CSS3 background-size - takes precendence over old resize
		// $w - absolute length or % or auto or cover | contain
		// $h - absolute length or % or auto or cover | contain
		if (isset($pba['w'])) {
			$cw = $pba['w'];
		}
		if (isset($pba['h'])) {
			$ch = $pba['h'];
		}

		$cw = $cw * fpdf_protection::SCALE;
		$ch = $ch * fpdf_protection::SCALE;
		if (empty($size) && !$resize) {
			return [$imw, $imh, $repx, $repy];
		}

		if (isset($size['w']) && $size['w']) {
			if ($size['w'] == 'contain') {
				// Scale the image, while preserving its intrinsic aspect ratio (if any),
				// to the largest size such that both its width and its height can fit inside the background positioning area.
				// Same as resize==3
				$h = $imh * $cw / $imw;
				$w = $cw;
				if ($h > $ch) {
					$w = $w * $ch / $h;
					$h = $ch;
				}
			} elseif ($size['w'] == 'cover') {
				// Scale the image, while preserving its intrinsic aspect ratio (if any),
				// to the smallest size such that both its width and its height can completely cover the background positioning area.
				$h = $imh * $cw / $imw;
				$w = $cw;
				if ($h < $ch) {
					$w = $w * $h / $ch;
					$h = $ch;
				}
			} else {
				if (stristr($size['w'], '%')) {
					$size['w'] = (float) $size['w'];
					$size['w'] /= 100;
					$size['w'] = ($cw * $size['w']);
				}
				if (stristr($size['h'], '%')) {
					$size['h'] = (float) $size['h'];
					$size['h'] /= 100;
					$size['h'] = ($ch * $size['h']);
				}
				if ($size['w'] == 'auto' && $size['h'] == 'auto') {
					$w = $imw;
					$h = $imh;
				} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
					$w = $imw * $size['h'] / $imh;
					$h = $size['h'];
				} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
					$h = $imh * $size['w'] / $imw;
					$w = $size['w'];
				} else {
					$w = $size['w'];
					$h = $size['h'];
				}
			}
			return [$w, $h, $repx, $repy];
		} elseif ($resize == 1 && $imw > $cw) {
			$h = $imh * $cw / $imw;
			return [$cw, $h, $repx, $repy];
		} elseif ($resize == 2 && $imh > $ch) {
			$w = $imw * $ch / $imh;
			return [$w, $ch, $repx, $repy];
		} elseif ($resize == 3) {
			$w = $imw;
			$h = $imh;
			if ($w > $cw) {
				$h = $h * $cw / $w;
				$w = $cw;
			}
			if ($h > $ch) {
				$w = $w * $ch / $h;
				$h = $ch;
			}
			return [$w, $h, $repx, $repy];
		} elseif ($resize == 4) {
			$h = $imh * $cw / $imw;
			return [$cw, $h, $repx, $repy];
		} elseif ($resize == 5) {
			$w = $imw * $ch / $imh;
			return [$w, $ch, $repx, $repy];
		} elseif ($resize == 6) {
			return [$cw, $ch, $repx, $repy];
		}
		return [$imw, $imh, $repx, $repy];
	}

	function PrintPageBackgrounds($adjustmenty = 0)
	{
		$s = '';

		ksort($this->pageBackgrounds);
		foreach ($this->pageBackgrounds as $bl => $pbs) {
			foreach ($pbs as $pb) {
				if ((!isset($pb['image_id']) && !isset($pb['gradient'])) || isset($pb['shadowonly'])) { // Background colour or boxshadow
					if ($pb['z-index'] > 0) {
						$this->current_layer = $pb['z-index'];
						$s .= "\n" . '/OCBZ-index /ZI' . $pb['z-index'] . ' BDC' . "\n";
					}

					if ($pb['visibility'] != 'visible') {
						if ($pb['visibility'] == 'printonly') {
							$s .= '/OC /OC1 BDC' . "\n";
						} elseif ($pb['visibility'] == 'screenonly') {
							$s .= '/OC /OC2 BDC' . "\n";
						} elseif ($pb['visibility'] == 'hidden') {
							$s .= '/OC /OC3 BDC' . "\n";
						}
					}
					// Box shadow
					if (isset($pb['shadow']) && $pb['shadow']) {
						$s .= $pb['shadow'] . "\n";
					}
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}
					$s .= 'q ' . $this->SetFColor($pb['col'], true) . "\n";
					if ($pb['col']{0} == 5) { // RGBa
						$s .= $this->SetAlpha(ord($pb['col']{4}) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($pb['col']{0} == 6) { // CMYKa
						$s .= $this->SetAlpha(ord($pb['col']{5}) / 100, 'Normal', true, 'F') . "\n";
					}
					$s .= sprintf('%.3F %.3F %.3F %.3F re f Q', $pb['x'] * fpdf_protection::SCALE, ($this->h - $pb['y']) * fpdf_protection::SCALE, $pb['w'] * fpdf_protection::SCALE, -$pb['h'] * Mpdf::SCALE) . "\n";
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
					if ($pb['visibility'] != 'visible') {
						$s .= 'EMC' . "\n";
					}

					if ($pb['z-index'] > 0) {
						$s .= "\n" . 'EMCBZ-index' . "\n";
						$this->current_layer = 0;
					}
				}
			}
			/* -- BACKGROUNDS -- */
			foreach ($pbs as $pb) {
				if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
					if ($pb['z-index'] > 0) {
						$this->current_layer = $pb['z-index'];
						$s .= "\n" . '/OCGZ-index /ZI' . $pb['z-index'] . ' BDC' . "\n";
					}
					if ($pb['visibility'] != 'visible') {
						if ($pb['visibility'] == 'printonly') {
							$s .= '/OC /OC1 BDC' . "\n";
						} elseif ($pb['visibility'] == 'screenonly') {
							$s .= '/OC /OC2 BDC' . "\n";
						} elseif ($pb['visibility'] == 'hidden') {
							$s .= '/OC /OC3 BDC' . "\n";
						}
					}
				}
				if (isset($pb['gradient']) && $pb['gradient']) {
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}
					$s .= $this->gradient->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				} elseif (isset($pb['image_id']) && $pb['image_id']) { // Background Image
					$pb['y'] -= $adjustmenty;
					$pb['h'] += $adjustmenty;
					$n = count($this->patterns) + 1;
					list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat'], $pb['bpa'], $pb['size']);
					$this->patterns[$n] = ['x' => $pb['x'], 'y' => $pb['y'], 'w' => $pb['w'], 'h' => $pb['h'], 'pgh' => $this->h, 'image_id' => $pb['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $pb['x_pos'], 'y_pos' => $pb['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $pb['itype'], 'bpa' => $pb['bpa']];
					$x = $pb['x'] * fpdf_protection::SCALE;
					$y = ($this->h - $pb['y']) * fpdf_protection::SCALE;
					$w = $pb['w'] * fpdf_protection::SCALE;
					$h = -$pb['h'] * fpdf_protection::SCALE;
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}
					if ($this->writingHTMLfooter || $this->writingHTMLheader) { // Write each (tiles) image rather than use as a pattern
						$iw = $pb['orig_w'] / fpdf_protection::SCALE;
						$ih = $pb['orig_h'] / fpdf_protection::SCALE;

						$w = $pb['w'];
						$h = $pb['h'];
						$x0 = $pb['x'];
						$y0 = $pb['y'];

						if (isset($pb['bpa']) && $pb['bpa']) {
							$w = $pb['bpa']['w'];
							$h = $pb['bpa']['h'];
							$x0 = $pb['bpa']['x'];
							$y0 = $pb['bpa']['y'];
						}

						if (isset($pb['size']['w']) && $pb['size']['w']) {
							$size = $pb['size'];

							if ($size['w'] == 'contain') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest
								// size such that both its width and its height can fit inside the background positioning area.
								// Same as resize==3
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih > $pb['bpa']['h']) {
									$iw = $iw * $pb['bpa']['h'] / $ih;
									$ih = $pb['bpa']['h'];
								}
							} elseif ($size['w'] == 'cover') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest
								// size such that both its width and its height can completely cover the background positioning area.
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih < $pb['bpa']['h']) {
									$iw = $iw * $ih / $pb['bpa']['h'];
									$ih = $pb['bpa']['h'];
								}
							} else {
								if (NumericString::containsPercentChar($size['w'])) {
									$size['w'] = NumericString::removePercentChar($size['w']);
									$size['w'] /= 100;
									$size['w'] = ($pb['bpa']['w'] * $size['w']);
								}
								if (NumericString::containsPercentChar($size['h'])) {
									$size['h'] = NumericString::removePercentChar($size['h']);
									$size['h'] /= 100;
									$size['h'] = ($pb['bpa']['h'] * $size['h']);
								}
								if ($size['w'] == 'auto' && $size['h'] == 'auto') {
									$iw = $iw;
									$ih = $ih;
								} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
									$iw = $iw * $size['h'] / $ih;
									$ih = $size['h'];
								} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
									$ih = $ih * $size['w'] / $iw;
									$iw = $size['w'];
								} else {
									$iw = $size['w'];
									$ih = $size['h'];
								}
							}
						}

						// Number to repeat
						if ($pb['x_repeat']) {
							$nx = ceil($pb['w'] / $iw) + 1;
						} else {
							$nx = 1;
						}

						if ($pb['y_repeat']) {
							$ny = ceil($pb['h'] / $ih) + 1;
						} else {
							$ny = 1;
						}

						$x_pos = $pb['x_pos'];
						if (stristr($x_pos, '%')) {
							$x_pos = (float) $x_pos;
							$x_pos /= 100;
							$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos);
						}

						$y_pos = $pb['y_pos'];
						if (stristr($y_pos, '%')) {
							$y_pos = (float) $y_pos;
							$y_pos /= 100;
							$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos);
						}
						if ($nx > 1) {
							while ($x_pos > ($pb['x'] - $pb['bpa']['x'])) {
								$x_pos -= $iw;
							}
						}

						if ($ny > 1) {
							while ($y_pos > ($pb['y'] - $pb['bpa']['y'])) {
								$y_pos -= $ih;
							}
						}

						for ($xi = 0; $xi < $nx; $xi++) {
							for ($yi = 0; $yi < $ny; $yi++) {
								$x = $x0 + $x_pos + ($iw * $xi);
								$y = $y0 + $y_pos + ($ih * $yi);
								if ($pb['opacity'] > 0 && $pb['opacity'] < 1) {
									$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
								} else {
									$opac = '';
								}
								$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac, $iw * fpdf_protection::SCALE, $ih * fpdf_protection::SCALE, $x * fpdf_protection::SCALE, ($this->h - ($y + $ih)) * fpdf_protection::SCALE, $pb['image_id']) . "\n";
							}
						}
					} else {
						if (($pb['opacity'] > 0 || $pb['opacity'] === '0') && $pb['opacity'] < 1) {
							$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
						} else {
							$opac = '';
						}
						$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) . "\n";
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				}

				if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
					if ($pb['visibility'] != 'visible') {
						$s .= 'EMC' . "\n";
					}

					if ($pb['z-index'] > 0) {
						$s .= "\n" . 'EMCGZ-index' . "\n";
						$this->current_layer = 0;
					}
				}
			}
			/* -- END BACKGROUNDS -- */
		}
		return $s;
	}

	function ResetMargins()
	{
		// ReSet left, top margins
		if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation == 'P' && $this->CurOrientation == 'L') {
			if (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
				$this->tMargin = $this->orig_rMargin;
				$this->bMargin = $this->orig_lMargin;
			} else { // ODD	// OR NOT MIRRORING MARGINS/FOOTERS
				$this->tMargin = $this->orig_lMargin;
				$this->bMargin = $this->orig_rMargin;
			}
			$this->lMargin = $this->DeflMargin;
			$this->rMargin = $this->DefrMargin;
			$this->MarginCorrection = 0;
			$this->PageBreakTrigger = $this->h - $this->bMargin;
		} elseif (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
			$this->lMargin = $this->DefrMargin;
			$this->rMargin = $this->DeflMargin;
			$this->MarginCorrection = $this->DefrMargin - $this->DeflMargin;
		} else { // ODD	// OR NOT MIRRORING MARGINS/FOOTERS
			$this->lMargin = $this->DeflMargin;
			$this->rMargin = $this->DefrMargin;
			if ($this->mirrorMargins) {
				$this->MarginCorrection = $this->DeflMargin - $this->DefrMargin;
			}
		}
		$this->x = $this->lMargin;
	}

	function ClearFloats($clear, $blklvl = 0)
	{
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($blklvl, true);
		$end = $currpos = ($this->page * 1000 + $this->y);
		if ($clear == 'BOTH' && ($l_exists || $r_exists)) {
			$this->pageoutput[$this->page] = [];
			$end = max($l_max, $r_max, $currpos);
		} elseif ($clear == 'RIGHT' && $r_exists) {
			$this->pageoutput[$this->page] = [];
			$end = max($r_max, $currpos);
		} elseif ($clear == 'LEFT' && $l_exists) {
			$this->pageoutput[$this->page] = [];
			$end = max($l_max, $currpos);
		} else {
			return;
		}
		$old_page = $this->page;
		$new_page = intval($end / 1000);
		if ($old_page != $new_page) {
			$s = $this->PrintPageBackgrounds();
			// Writes after the marker so not overwritten later by page background etc.
			$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->pages[$this->page]);
			$this->pageBackgrounds = [];
			$this->page = $new_page;
		}
		$this->ResetMargins();
		$this->pageoutput[$this->page] = [];
		$this->y = (($end * 1000) % 1000000) / 1000; // mod changes operands to integers before processing
	}

	function newFlowingBlock($w, $h, $a = '', $is_table = false, $blockstate = 0, $newblock = true, $blockdir = 'ltr', $table_draft = false)
	{
		if (!$a) {
			if ($blockdir == 'rtl') {
				$a = 'R';
			} else {
				$a = 'L';
			}
		}
		$this->flowingBlockAttr['width'] = ($w * fpdf_protection::SCALE);
		// line height in user units
		$this->flowingBlockAttr['is_table'] = $is_table;
		$this->flowingBlockAttr['table_draft'] = $table_draft;
		$this->flowingBlockAttr['height'] = $h;
		$this->flowingBlockAttr['lineCount'] = 0;
		$this->flowingBlockAttr['align'] = $a;
		$this->flowingBlockAttr['font'] = [];
		$this->flowingBlockAttr['content'] = [];
		$this->flowingBlockAttr['contentB'] = [];
		$this->flowingBlockAttr['contentWidth'] = 0;
		$this->flowingBlockAttr['blockstate'] = $blockstate;

		$this->flowingBlockAttr['newblock'] = $newblock;
		$this->flowingBlockAttr['valign'] = 'M';
		$this->flowingBlockAttr['blockdir'] = $blockdir;
		$this->flowingBlockAttr['cOTLdata'] = []; // mPDF 5.7.1
		$this->flowingBlockAttr['lastBidiText'] = ''; // mPDF 5.7.1
		if (!empty($this->otl)) {
			$this->otl->lastBidiStrongType = '';
		} // *OTL*
	}

	function _setLineYpos(&$fontsize, &$fontdesc, &$CSSlineheight, $blockYpos = false)
	{
		$ypos['glyphYorigin'] = 0;
		$ypos['baseline-shift'] = 0;
		$linegap = 0;
		$leading = 0;

		if (isset($fontdesc['Ascent']) && $fontdesc['Ascent'] && !$this->useFixedTextBaseline) {
			// Fontsize uses font metrics - this method seems to produce results compatible with browsers (except IE9)
			$ypos['boxtop'] = $fontdesc['Ascent'] / 1000 * $fontsize;
			$ypos['boxbottom'] = $fontdesc['Descent'] / 1000 * $fontsize;
			if (isset($fontdesc['Leading'])) {
				$linegap = $fontdesc['Leading'] / 1000 * $fontsize;
			}
		} // Default if not set - uses baselineC
		else {
			$ypos['boxtop'] = (0.5 + $this->baselineC) * $fontsize;
			$ypos['boxbottom'] = -(0.5 - $this->baselineC) * $fontsize;
		}
		$fontheight = $ypos['boxtop'] - $ypos['boxbottom'];

		if ($this->shrin_k > 1) {
			$shrin_k = $this->shrin_k;
		} else {
			$shrin_k = 1;
		}

		$leading = 0;
		if ($CSSlineheight == 'N') {
			$lh = $this->_getNormalLineheight($fontdesc);
			$lineheight = ($fontsize * $lh);
			$leading += $linegap; // specified in hhea or sTypo in OpenType tables
		} elseif (preg_match('/mm/', $CSSlineheight)) {
			$lineheight = (((float) $CSSlineheight) / $shrin_k); // convert to number
		} // ??? If lineheight is a factor e.g. 1.3  ?? use factor x 1em or ? use 'normal' lineheight * factor
		// Could depend on value for $text_height - a draft CSS value as set above for now
		elseif ($CSSlineheight > 0) {
			$lineheight = ($fontsize * $CSSlineheight);
		} else {
			$lineheight = ($fontsize * $this->normalLineheight);
		}

		// In general, calculate the "leading" - the difference between the fontheight and the lineheight
		// and add half to the top and half to the bottom. BUT
		// If an inline element has a font-size less than the block element, and the line-height is set as an em or % value
		// it will add too much leading below the font and expand the height of the line - so just use the block element exttop/extbottom:
		if (preg_match('/mm/', $CSSlineheight) && $ypos['boxtop'] < $blockYpos['boxtop'] && $ypos['boxbottom'] > $blockYpos['boxbottom']) {
			$ypos['exttop'] = $blockYpos['exttop'];
			$ypos['extbottom'] = $blockYpos['extbottom'];
		} else {
			$leading += ($lineheight - $fontheight);

			$ypos['exttop'] = $ypos['boxtop'] + $leading / 2;
			$ypos['extbottom'] = $ypos['boxbottom'] - $leading / 2;
		}


		// TEMP ONLY FOR DEBUGGING *********************************
		// $ypos['lineheight'] = $lineheight;
		// $ypos['fontheight'] = $fontheight;
		// $ypos['leading'] = $leading;

		return $ypos;
	}

	function _setInlineBlockHeights(&$lineBox, &$stackHeight, &$content, &$font, $is_table)
	{
		if ($this->shrin_k > 1) {
			$shrin_k = $this->shrin_k;
		} else {
			$shrin_k = 1;
		}

		$ypos = [];
		$bordypos = [];
		$bgypos = [];

		if ($is_table) {
			// FOR TABLE
			$fontsize = $this->FontSize;
			$fontkey = $this->FontFamily . $this->FontStyle;
			$fontdesc = $this->fonts[$fontkey]['desc'];
			$CSSlineheight = $this->cellLineHeight;
			$line_stacking_strategy = $this->cellLineStackingStrategy; // inline-line-height [default] | block-line-height | max-height | grid-height
			$line_stacking_shift = $this->cellLineStackingShift;  // consider-shifts [default] | disregard-shifts
		} else {
			// FOR BLOCK FONT
			$fontsize = $this->blk[$this->blklvl]['InlineProperties']['size'];
			$fontkey = $this->blk[$this->blklvl]['InlineProperties']['family'] . $this->blk[$this->blklvl]['InlineProperties']['style'];
			$fontdesc = $this->fonts[$fontkey]['desc'];
			$CSSlineheight = $this->blk[$this->blklvl]['line_height'];
			// inline-line-height | block-line-height | max-height | grid-height
			$line_stacking_strategy = (isset($this->blk[$this->blklvl]['line_stacking_strategy']) ? $this->blk[$this->blklvl]['line_stacking_strategy'] : 'inline-line-height');
			// consider-shifts | disregard-shifts
			$line_stacking_shift = (isset($this->blk[$this->blklvl]['line_stacking_shift']) ? $this->blk[$this->blklvl]['line_stacking_shift'] : 'consider-shifts');
		}
		$boxLineHeight = $this->_computeLineheight($CSSlineheight, $fontsize);


		// First, set a "strut" using block font at index $lineBox[-1]
		$ypos[-1] = $this->_setLineYpos($fontsize, $fontdesc, $CSSlineheight);

		// for the block element - always taking the block EXTENDED progression including leading - which may be negative
		if ($line_stacking_strategy == 'block-line-height') {
			$topy = $ypos[-1]['exttop'];
			$bottomy = $ypos[-1]['extbottom'];
		} else {
			$topy = 0;
			$bottomy = 0;
		}

		// Get text-middle for aligning images/objects
		$midpoint = $ypos[-1]['boxtop'] - (($ypos[-1]['boxtop'] - $ypos[-1]['boxbottom']) / 2);

		// for images / inline objects / replaced elements
		$mta = 0; // Maximum top-aligned
		$mba = 0; // Maximum bottom-aligned
		foreach ($content as $k => $chunk) {
			if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]['type'] == 'listmarker') {
				$ypos[$k] = $ypos[-1];
				// UPDATE Maximums
				if ($line_stacking_strategy == 'block-line-height' || $line_stacking_strategy == 'grid-height' || $line_stacking_strategy == 'max-height') { // don't include extended block progression of all inline elements
					if ($ypos[$k]['boxtop'] > $topy) {
						$topy = $ypos[$k]['boxtop'];
					}
					if ($ypos[$k]['boxbottom'] < $bottomy) {
						$bottomy = $ypos[$k]['boxbottom'];
					}
				} else {
					if ($ypos[$k]['exttop'] > $topy) {
						$topy = $ypos[$k]['exttop'];
					}
					if ($ypos[$k]['extbottom'] < $bottomy) {
						$bottomy = $ypos[$k]['extbottom'];
					}
				}
			} elseif (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
				$fontsize = $font[$k]['size'];
				$fontdesc = $font[$k]['curr']['desc'];
				$lh = 1;
				$ypos[$k] = $this->_setLineYpos($fontsize, $fontdesc, $lh, $ypos[-1]); // Lineheight=1 fixed
			} elseif (isset($this->objectbuffer[$k])) {
				$oh = $this->objectbuffer[$k]['OUTER-HEIGHT'];
				$va = $this->objectbuffer[$k]['vertical-align'];

				if ($va == 'BS') { //  (BASELINE default)
					if ($oh > $topy) {
						$topy = $oh;
					}
				} elseif ($va == 'M') {
					if (($midpoint + $oh / 2) > $topy) {
						$topy = $midpoint + $oh / 2;
					}
					if (($midpoint - $oh / 2) < $bottomy) {
						$bottomy = $midpoint - $oh / 2;
					}
				} elseif ($va == 'TT') {
					if (($ypos[-1]['boxtop'] - $oh) < $bottomy) {
						$bottomy = $ypos[-1]['boxtop'] - $oh;
						$topy = max($topy, $ypos[-1]['boxtop']);
					}
				} elseif ($va == 'TB') {
					if (($ypos[-1]['boxbottom'] + $oh) > $topy) {
						$topy = $ypos[-1]['boxbottom'] + $oh;
						$bottomy = min($bottomy, $ypos[-1]['boxbottom']);
					}
				} elseif ($va == 'T') {
					if ($oh > $mta) {
						$mta = $oh;
					}
				} elseif ($va == 'B') {
					if ($oh > $mba) {
						$mba = $oh;
					}
				}
			} elseif ($content[$k] || $content[$k] === '0') {
				// FOR FLOWING BLOCK
				$fontsize = $font[$k]['size'];
				$fontdesc = $font[$k]['curr']['desc'];
				// In future could set CSS line-height from inline elements; for now, use block level:
				$ypos[$k] = $this->_setLineYpos($fontsize, $fontdesc, $CSSlineheight, $ypos[-1]);

				if (isset($font[$k]['textparam']['text-baseline']) && $font[$k]['textparam']['text-baseline'] != 0) {
					$ypos[$k]['baseline-shift'] = $font[$k]['textparam']['text-baseline'];
				}

				// DO ALIGNMENT FOR BASELINES *******************
				// Until most fonts have OpenType BASE tables, this won't work
				// $ypos[$k] compared to $ypos[-1] or $ypos[$k-1] using $dominant_baseline and $baseline_table
				// UPDATE Maximums
				if ($line_stacking_strategy == 'block-line-height' || $line_stacking_strategy == 'grid-height' || $line_stacking_strategy == 'max-height') { // don't include extended block progression of all inline elements
					if ($line_stacking_shift == 'disregard-shifts') {
						if ($ypos[$k]['boxtop'] > $topy) {
							$topy = $ypos[$k]['boxtop'];
						}
						if ($ypos[$k]['boxbottom'] < $bottomy) {
							$bottomy = $ypos[$k]['boxbottom'];
						}
					} else {
						if (($ypos[$k]['boxtop'] + $ypos[$k]['baseline-shift']) > $topy) {
							$topy = $ypos[$k]['boxtop'] + $ypos[$k]['baseline-shift'];
						}
						if (($ypos[$k]['boxbottom'] + $ypos[$k]['baseline-shift']) < $bottomy) {
							$bottomy = $ypos[$k]['boxbottom'] + $ypos[$k]['baseline-shift'];
						}
					}
				} else {
					if ($line_stacking_shift == 'disregard-shifts') {
						if ($ypos[$k]['exttop'] > $topy) {
							$topy = $ypos[$k]['exttop'];
						}
						if ($ypos[$k]['extbottom'] < $bottomy) {
							$bottomy = $ypos[$k]['extbottom'];
						}
					} else {
						if (($ypos[$k]['exttop'] + $ypos[$k]['baseline-shift']) > $topy) {
							$topy = $ypos[$k]['exttop'] + $ypos[$k]['baseline-shift'];
						}
						if (($ypos[$k]['extbottom'] + $ypos[$k]['baseline-shift']) < $bottomy) {
							$bottomy = $ypos[$k]['extbottom'] + $ypos[$k]['baseline-shift'];
						}
					}
				}

				// If BORDER set on inline element
				if (isset($font[$k]['bord']) && $font[$k]['bord']) {
					$bordfontsize = $font[$k]['textparam']['bord-decoration']['fontsize'] / $shrin_k;
					$bordfontkey = $font[$k]['textparam']['bord-decoration']['fontkey'];
					if ($bordfontkey != $fontkey || $bordfontsize != $fontsize || isset($font[$k]['textparam']['bord-decoration']['baseline'])) {
						$bordfontdesc = $this->fonts[$bordfontkey]['desc'];
						$bordypos[$k] = $this->_setLineYpos($bordfontsize, $bordfontdesc, $CSSlineheight, $ypos[-1]);
						if (isset($font[$k]['textparam']['bord-decoration']['baseline']) && $font[$k]['textparam']['bord-decoration']['baseline'] != 0) {
							$bordypos[$k]['baseline-shift'] = $font[$k]['textparam']['bord-decoration']['baseline'] / $shrin_k;
						}
					}
				}
				// If BACKGROUND set on inline element
				if (isset($font[$k]['spanbgcolor']) && $font[$k]['spanbgcolor']) {
					$bgfontsize = $font[$k]['textparam']['bg-decoration']['fontsize'] / $shrin_k;
					$bgfontkey = $font[$k]['textparam']['bg-decoration']['fontkey'];
					if ($bgfontkey != $fontkey || $bgfontsize != $fontsize || isset($font[$k]['textparam']['bg-decoration']['baseline'])) {
						$bgfontdesc = $this->fonts[$bgfontkey]['desc'];
						$bgypos[$k] = $this->_setLineYpos($bgfontsize, $bgfontdesc, $CSSlineheight, $ypos[-1]);
						if (isset($font[$k]['textparam']['bg-decoration']['baseline']) && $font[$k]['textparam']['bg-decoration']['baseline'] != 0) {
							$bgypos[$k]['baseline-shift'] = $font[$k]['textparam']['bg-decoration']['baseline'] / $shrin_k;
						}
					}
				}
			}
		}


		// TOP or BOTTOM aligned images
		if ($mta > ($topy - $bottomy)) {
			if (($topy - $mta) < $bottomy) {
				$bottomy = $topy - $mta;
			}
		}
		if ($mba > ($topy - $bottomy)) {
			if (($bottomy + $mba) > $topy) {
				$topy = $bottomy + $mba;
			}
		}

		if ($line_stacking_strategy == 'block-line-height') { // fixed height set by block element (whether present or not)
			$topy = $ypos[-1]['exttop'];
			$bottomy = $ypos[-1]['extbottom'];
		}

		$inclusiveHeight = $topy - $bottomy;

		// SET $stackHeight taking note of line_stacking_strategy
		// NB inclusive height already takes account of need to consider block progression height (excludes leading set by lineheight)
		// or extended block progression height (includes leading set by lineheight)
		if ($line_stacking_strategy == 'block-line-height') { // fixed = extended block progression height of block element
			$stackHeight = $boxLineHeight;
		} elseif ($line_stacking_strategy == 'max-height') { // smallest height which includes extended block progression height of block element
			// and block progression heights of inline elements (NOT extended)
			$stackHeight = $inclusiveHeight;
		} elseif ($line_stacking_strategy == 'grid-height') { // smallest multiple of block element lineheight to include
			// block progression heights of inline elements (NOT extended)
			$stackHeight = $boxLineHeight;
			while ($stackHeight < $inclusiveHeight) {
				$stackHeight += $boxLineHeight;
			}
		} else { // 'inline-line-height' = default		// smallest height which includes extended block progression height of block element
			// AND extended block progression heights of inline elements
			$stackHeight = $inclusiveHeight;
		}

		$diff = $stackHeight - $inclusiveHeight;
		$topy += $diff / 2;
		$bottomy -= $diff / 2;

		// ADJUST $ypos => lineBox using $stackHeight; lineBox are all offsets from the top of stackHeight in mm
		// and SET IMAGE OFFSETS
		$lineBox[-1]['boxtop'] = $topy - $ypos[-1]['boxtop'];
		$lineBox[-1]['boxbottom'] = $topy - $ypos[-1]['boxbottom'];
		// $lineBox[-1]['exttop'] = $topy - $ypos[-1]['exttop'];
		// $lineBox[-1]['extbottom'] = $topy - $ypos[-1]['extbottom'];
		$lineBox[-1]['glyphYorigin'] = $topy - $ypos[-1]['glyphYorigin'];
		$lineBox[-1]['baseline-shift'] = $ypos[-1]['baseline-shift'];

		$midpoint = $lineBox[-1]['boxbottom'] - (($lineBox[-1]['boxbottom'] - $lineBox[-1]['boxtop']) / 2);

		foreach ($content as $k => $chunk) {
			if (isset($this->objectbuffer[$k])) {
				$oh = $this->objectbuffer[$k]['OUTER-HEIGHT'];
				// LIST MARKERS
				if ($this->objectbuffer[$k]['type'] == 'listmarker') {
					$oh = $fontsize;
				} elseif ($this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
					$oh = $font[$k]['size']; // == $this->objectbuffer[$k]['fontsize']/Mpdf::SCALE;
					$lineBox[$k]['boxtop'] = $topy - $ypos[$k]['boxtop'];
					$lineBox[$k]['boxbottom'] = $topy - $ypos[$k]['boxbottom'];
					$lineBox[$k]['glyphYorigin'] = $topy - $ypos[$k]['glyphYorigin'];
					$lineBox[$k]['baseline-shift'] = 0;
					// continue;
				}
				$va = $this->objectbuffer[$k]['vertical-align']; // = $objattr['vertical-align'] = set as M,T,B,S

				if ($va == 'BS') { //  (BASELINE default)
					$lineBox[$k]['top'] = $lineBox[-1]['glyphYorigin'] - $oh;
				} elseif ($va == 'M') {
					$lineBox[$k]['top'] = $midpoint - $oh / 2;
				} elseif ($va == 'TT') {
					$lineBox[$k]['top'] = $lineBox[-1]['boxtop'];
				} elseif ($va == 'TB') {
					$lineBox[$k]['top'] = $lineBox[-1]['boxbottom'] - $oh;
				} elseif ($va == 'T') {
					$lineBox[$k]['top'] = 0;
				} elseif ($va == 'B') {
					$lineBox[$k]['top'] = $stackHeight - $oh;
				}
			} elseif ($content[$k] || $content[$k] === '0') {
				$lineBox[$k]['boxtop'] = $topy - $ypos[$k]['boxtop'];
				$lineBox[$k]['boxbottom'] = $topy - $ypos[$k]['boxbottom'];
				// $lineBox[$k]['exttop'] = $topy - $ypos[$k]['exttop'];
				// $lineBox[$k]['extbottom'] = $topy - $ypos[$k]['extbottom'];
				$lineBox[$k]['glyphYorigin'] = $topy - $ypos[$k]['glyphYorigin'];
				$lineBox[$k]['baseline-shift'] = $ypos[$k]['baseline-shift'];
				if (isset($bordypos[$k]['boxtop'])) {
					$lineBox[$k]['border-boxtop'] = $topy - $bordypos[$k]['boxtop'];
					$lineBox[$k]['border-boxbottom'] = $topy - $bordypos[$k]['boxbottom'];
					$lineBox[$k]['border-baseline-shift'] = $bordypos[$k]['baseline-shift'];
				}
				if (isset($bgypos[$k]['boxtop'])) {
					$lineBox[$k]['background-boxtop'] = $topy - $bgypos[$k]['boxtop'];
					$lineBox[$k]['background-boxbottom'] = $topy - $bgypos[$k]['boxbottom'];
					$lineBox[$k]['background-baseline-shift'] = $bgypos[$k]['baseline-shift'];
				}
			}
		}
	}

	function SetCol($CurrCol)
	{
		// Used internally to set column by number: 0 is 1st column
		// Set position on a column
		$this->CurrCol = $CurrCol;
		$x = $this->ColL[$CurrCol];
		$xR = $this->ColR[$CurrCol]; // NB This is not R margin -> R pos
		if (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
			$x += $this->MarginCorrection;
			$xR += $this->MarginCorrection;
		}
		$this->SetMargins($x, ($this->w - $xR), $this->tMargin);
	}

	function ResetSpacing()
	{
		if ($this->ws != 0) {
			$this->_out('BT 0 Tw ET');
		}
		$this->ws = 0;
		if ($this->charspacing != 0) {
			$this->_out('BT 0 Tc ET');
		}
		$this->charspacing = 0;
	}

	function SetSpacing($cs, $ws)
	{
		if (intval($cs * 1000) == 0) {
			$cs = 0;
		}
		if ($cs) {
			$this->_out(sprintf('BT %.3F Tc ET', $cs));
		} elseif ($this->charspacing != 0) {
			$this->_out('BT 0 Tc ET');
		}
		$this->charspacing = $cs;
		if (intval($ws * 1000) == 0) {
			$ws = 0;
		}
		if ($ws) {
			$this->_out(sprintf('BT %.3F Tw ET', $ws));
		} elseif ($this->ws != 0) {
			$this->_out('BT 0 Tw ET');
		}
		$this->ws = $ws;
	}

	function GetFirstBlockFill()
	{
		// Returns the first blocklevel that uses a bgcolor fill
		$startfill = 0;
		for ($i = 1; $i <= $this->blklvl; $i++) {
			if ($this->blk[$i]['bgcolor'] || $this->blk[$i]['border_left']['w'] || $this->blk[$i]['border_right']['w'] || $this->blk[$i]['border_top']['w'] || $this->blk[$i]['border_bottom']['w']) {
				$startfill = $i;
				break;
			}
		}
		return $startfill;
	}

	function _setBorderLine($b, $k = 1)
	{
		$this->SetLineWidth($b['w'] / $k);
		$this->SetDColor($b['c']);
		if ($b['c'][0] == 5) { // RGBa
			$this->SetAlpha(ord($b['c'][4]) / 100, 'Normal', false, 'S'); // mPDF 5.7.2
		} elseif ($b['c'][0] == 6) { // CMYKa
			$this->SetAlpha(ord($b['c'][5]) / 100, 'Normal', false, 'S'); // mPDF 5.7.2
		}
	}

	function SetLineJoin($mode = 0)
	{
		$s = sprintf('%d j', $mode);
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['LineJoin']) && $this->pageoutput[$this->page]['LineJoin'] != $s) || !isset($this->pageoutput[$this->page]['LineJoin']))) {
			$this->_out($s);
		}
		$this->pageoutput[$this->page]['LineJoin'] = $s;
	}

	function SetLineCap($mode = 2)
	{
		$s = sprintf('%d J', $mode);
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['LineCap']) && $this->pageoutput[$this->page]['LineCap'] != $s) || !isset($this->pageoutput[$this->page]['LineCap']))) {
			$this->_out($s);
		}
		$this->pageoutput[$this->page]['LineCap'] = $s;
	}

	function _setDashBorder($style, $div, $cp, $side)
	{
		if ($style == 'dashed' && (($side == 'L' || $side == 'R') || ($side == 'T' && $div != 'pagetop' && !$cp) || ($side == 'B' && $div != 'pagebottom') )) {
			$dashsize = 2; // final dash will be this + 1*linewidth
			$dashsizek = 1.5; // ratio of Dash/Blank
			$this->SetDash($dashsize, ($dashsize / $dashsizek) + ($this->LineWidth * 2));
		} elseif ($style == 'dotted' || ($side == 'T' && ($div == 'pagetop' || $cp)) || ($side == 'B' && $div == 'pagebottom')) {
			// Round join and cap
			$this->SetLineJoin(1);
			$this->SetLineCap(1);
			$this->SetDash(0.001, ($this->LineWidth * 3));
		}
	}

	function PaintDivBB($divider = '', $blockstate = 0, $blvl = 0)
	{
		// Borders & backgrounds are done elsewhere for columns - messes up the repositioning in printcolumnbuffer
		if ($this->ColActive) {
			return;
		} // *COLUMNS*
		if ($this->keep_block_together) {
			return;
		} // mPDF 6
		$save_y = $this->y;
		if (!$blvl) {
			$blvl = $this->blklvl;
		}
		$x0 = $x1 = $y0 = $y1 = 0;

		// Added mPDF 3.0 Float DIV
		if (isset($this->blk[$blvl]['bb_painted'][$this->page]) && $this->blk[$blvl]['bb_painted'][$this->page]) {
			return;
		} // *CSS-FLOAT*

		if (isset($this->blk[$blvl]['x0'])) {
			$x0 = $this->blk[$blvl]['x0'];
		} // left
		if (isset($this->blk[$blvl]['y1'])) {
			$y1 = $this->blk[$blvl]['y1'];
		} // bottom
		// Added mPDF 3.0 Float DIV - ensures backgrounds/borders are drawn to bottom of page
		if ($y1 == 0) {
			if ($divider == 'pagebottom') {
				$y1 = $this->h - $this->bMargin;
			} else {
				$y1 = $this->y;
			}
		}

		if (isset($this->blk[$blvl]['startpage']) && $this->blk[$blvl]['startpage'] != $this->page) {
			$continuingpage = true;
		} else {
			$continuingpage = false;
		}

		if (isset($this->blk[$blvl]['y0'])) {
			$y0 = $this->blk[$blvl]['y0'];
		}
		$h = $y1 - $y0;
		$w = $this->blk[$blvl]['width'];
		$x1 = $x0 + $w;

		// Set border-widths as used here
		$border_top = $this->blk[$blvl]['border_top']['w'];
		$border_bottom = $this->blk[$blvl]['border_bottom']['w'];
		$border_left = $this->blk[$blvl]['border_left']['w'];
		$border_right = $this->blk[$blvl]['border_right']['w'];
		if (!$this->blk[$blvl]['border_top'] || $divider == 'pagetop' || $continuingpage) {
			$border_top = 0;
		}
		if (!$this->blk[$blvl]['border_bottom'] || $blockstate == 1 || $divider == 'pagebottom') {
			$border_bottom = 0;
		}

		$brTL_H = 0;
		$brTL_V = 0;
		$brTR_H = 0;
		$brTR_V = 0;
		$brBL_H = 0;
		$brBL_V = 0;
		$brBR_H = 0;
		$brBR_V = 0;

		$brset = false;
		/* -- BORDER-RADIUS -- */
		if (isset($this->blk[$blvl]['border_radius_TL_H'])) {
			$brTL_H = $this->blk[$blvl]['border_radius_TL_H'];
			$brset = true;
		}
		if (isset($this->blk[$blvl]['border_radius_TL_V'])) {
			$brTL_V = $this->blk[$blvl]['border_radius_TL_V'];
			$brset = true;
		}
		if (isset($this->blk[$blvl]['border_radius_TR_H'])) {
			$brTR_H = $this->blk[$blvl]['border_radius_TR_H'];
			$brset = true;
		}
		if (isset($this->blk[$blvl]['border_radius_TR_V'])) {
			$brTR_V = $this->blk[$blvl]['border_radius_TR_V'];
			$brset = true;
		}
		if (isset($this->blk[$blvl]['border_radius_BR_H'])) {
			$brBR_H = $this->blk[$blvl]['border_radius_BR_H'];
			$brset = true;
		}
		if (isset($this->blk[$blvl]['border_radius_BR_V'])) {
			$brBR_V = $this->blk[$blvl]['border_radius_BR_V'];
			$brset = true;
		}
		if (isset($this->blk[$blvl]['border_radius_BL_H'])) {
			$brBL_H = $this->blk[$blvl]['border_radius_BL_H'];
			$brset = true;
		}
		if (isset($this->blk[$blvl]['border_radius_BL_V'])) {
			$brBL_V = $this->blk[$blvl]['border_radius_BL_V'];
			$brset = true;
		}

		if (!$this->blk[$blvl]['border_top'] || $divider == 'pagetop' || $continuingpage) {
			$brTL_H = 0;
			$brTL_V = 0;
			$brTR_H = 0;
			$brTR_V = 0;
		}
		if (!$this->blk[$blvl]['border_bottom'] || $blockstate == 1 || $divider == 'pagebottom') {
			$brBL_H = 0;
			$brBL_V = 0;
			$brBR_H = 0;
			$brBR_V = 0;
		}

		// Disallow border-radius if it is smaller than the border width.
		if ($brTL_H < min($border_left, $border_top)) {
			$brTL_H = $brTL_V = 0;
		}
		if ($brTL_V < min($border_left, $border_top)) {
			$brTL_V = $brTL_H = 0;
		}
		if ($brTR_H < min($border_right, $border_top)) {
			$brTR_H = $brTR_V = 0;
		}
		if ($brTR_V < min($border_right, $border_top)) {
			$brTR_V = $brTR_H = 0;
		}
		if ($brBL_H < min($border_left, $border_bottom)) {
			$brBL_H = $brBL_V = 0;
		}
		if ($brBL_V < min($border_left, $border_bottom)) {
			$brBL_V = $brBL_H = 0;
		}
		if ($brBR_H < min($border_right, $border_bottom)) {
			$brBR_H = $brBR_V = 0;
		}
		if ($brBR_V < min($border_right, $border_bottom)) {
			$brBR_V = $brBR_H = 0;
		}

		// CHECK FOR radii that sum to > width or height of div ********
		$f = min($h / ($brTL_V + $brBL_V + 0.001), $h / ($brTR_V + $brBR_V + 0.001), $w / ($brTL_H + $brTR_H + 0.001), $w / ($brBL_H + $brBR_H + 0.001));
		if ($f < 1) {
			$brTL_H *= $f;
			$brTL_V *= $f;
			$brTR_H *= $f;
			$brTR_V *= $f;
			$brBL_H *= $f;
			$brBL_V *= $f;
			$brBR_H *= $f;
			$brBR_V *= $f;
		}
		/* -- END BORDER-RADIUS -- */

		$tbcol = $this->colorConverter->convert(255, $this->PDFAXwarnings);
		for ($l = 0; $l <= $blvl; $l++) {
			if ($this->blk[$l]['bgcolor']) {
				$tbcol = $this->blk[$l]['bgcolorarray'];
			}
		}

		// BORDERS
		if (isset($this->blk[$blvl]['y0']) && $this->blk[$blvl]['y0']) {
			$y0 = $this->blk[$blvl]['y0'];
		}
		$h = $y1 - $y0;
		$w = $this->blk[$blvl]['width'];

		if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
			$tbd = $this->blk[$blvl]['border_top'];

			$legend = '';
			$legbreakL = 0;
			$legbreakR = 0;
			// BORDER LEGEND
			if (isset($this->blk[$blvl]['border_legend']) && $this->blk[$blvl]['border_legend']) {
				$legend = $this->blk[$blvl]['border_legend']; // Same structure array as textbuffer
				$txt = $legend[0] = ltrim($legend[0]);
				if (!empty($legend[18])) {
					$this->otl->trimOTLdata($legend[18], true, false);
				} // *OTL*
				// Set font, size, style, color
				$this->SetFont($legend[4], $legend[2], $legend[11]);
				if (isset($legend[3]) && $legend[3]) {
					$cor = $legend[3];
					$this->SetTColor($cor);
				}
				$stringWidth = $this->GetStringWidth($txt, true, $legend[18], $legend[8]);
				$save_x = $this->x;
				$save_y = $this->y;
				$save_currentfontfamily = $this->FontFamily;
				$save_currentfontsize = $this->FontSizePt;
				$save_currentfontstyle = $this->FontStyle;
				$this->y = $y0 - $this->FontSize / 2 + $this->blk[$blvl]['border_top']['w'] / 2;
				$this->x = $x0 + $this->blk[$blvl]['padding_left'] + $this->blk[$blvl]['border_left']['w'];

				// Set the distance from the border line to the text ? make configurable variable
				$gap = 0.2 * $this->FontSize;
				$legbreakL = $this->x - $gap;
				$legbreakR = $this->x + $stringWidth + $gap;
				$this->magic_reverse_dir($txt, $this->blk[$blvl]['direction'], $legend[18]);
				$fill = '';
				$this->Cell($stringWidth, $this->FontSize, $txt, '', 0, 'C', $fill, '', 0, 0, 0, 'M', $fill, false, $legend[18], $legend[8]);
				// Reset
				$this->x = $save_x;
				$this->y = $save_y;
				$this->SetFont($save_currentfontfamily, $save_currentfontstyle, $save_currentfontsize);
				$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			}

			if (isset($tbd['s']) && $tbd['s']) {
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('q');
					$this->SetLineWidth(0);
					$this->_out(sprintf('%.3F %.3F m ', ($x0) * Mpdf::SCALE, ($this->h - ($y0)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $border_left) * Mpdf::SCALE, ($this->h - ($y0 + $border_top)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $w - $border_right) * Mpdf::SCALE, ($this->h - ($y0 + $border_top)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $w) * Mpdf::SCALE, ($this->h - ($y0)) * Mpdf::SCALE));
					$this->_out(' h W n '); // Ends path no-op & Sets the clipping path
				}

				$this->_setBorderLine($tbd);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$legbreakL -= $border_top / 2; // because line cap different
					$legbreakR += $border_top / 2;
					$this->_setDashBorder($tbd['style'], $divider, $continuingpage, 'T');
				} /* -- BORDER-RADIUS -- */ elseif (($brTL_V && $brTL_H) || ($brTR_V && $brTR_H) || $tbd['style'] == 'solid' || $tbd['style'] == 'double') {
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
				}
				$s = '';
				if ($brTR_H && $brTR_V) {
					$s .= ($this->_EllipseArc($x0 + $w - $brTR_H, $y0 + $brTR_V, $brTR_H - $border_top / 2, $brTR_V - $border_top / 2, 1, 2, true)) . "\n";
				} else { 				/* -- END BORDER-RADIUS -- */
					if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F m ', ($x0 + $w) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F m ', ($x0 + $w - ($border_top / 2)) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
					}
				}
				/* -- BORDER-RADIUS -- */
				if ($brTL_H && $brTL_V) {
					if ($legend) {
						if ($legbreakR < ($x0 + $w - $brTR_H)) {
							$s .= (sprintf('%.3F %.3F l ', $legbreakR * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
						}
						if ($legbreakL > ($x0 + $brTL_H )) {
							$s .= (sprintf('%.3F %.3F m ', $legbreakL * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
							$s .= (sprintf('%.3F %.3F l ', ($x0 + $brTL_H ) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE) . "\n");
						} else {
							$s .= (sprintf('%.3F %.3F m ', ($x0 + $brTL_H ) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
						}
					} else {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + $brTL_H ) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
					}
					$s .= ($this->_EllipseArc($x0 + $brTL_H, $y0 + $brTL_V, $brTL_H - $border_top / 2, $brTL_V - $border_top / 2, 2, 1)) . "\n";
				} else {
					/* -- END BORDER-RADIUS -- */
					if ($legend) {
						if ($legbreakR < ($x0 + $w)) {
							$s .= (sprintf('%.3F %.3F l ', $legbreakR * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
						}
						if ($legbreakL > ($x0)) {
							$s .= (sprintf('%.3F %.3F m ', $legbreakL * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
							if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
								$s .= (sprintf('%.3F %.3F l ', ($x0) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
							} else {
								$s .= (sprintf('%.3F %.3F l ', ($x0 + ($border_top / 2)) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
							}
						} elseif ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
							$s .= (sprintf('%.3F %.3F m ', ($x0) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
						} else {
							$s .= (sprintf('%.3F %.3F m ', ($x0 + $border_top / 2) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
						}
					} elseif ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F l ', ($x0) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + ($border_top / 2)) * Mpdf::SCALE, ($this->h - ($y0 + ($border_top / 2))) * Mpdf::SCALE)) . "\n";
					}
					/* -- BORDER-RADIUS -- */
				}
				/* -- END BORDER-RADIUS -- */
				$s .= 'S' . "\n";
				$this->_out($s);

				if ($tbd['style'] == 'double') {
					$this->SetLineWidth($tbd['w'] / 3);
					$this->SetDColor($tbcol);
					$this->_out($s);
				}
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('Q');
				}

				// Reset Corners and Dash off
				$this->SetLineWidth(0.1);
				$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}
		// Reinstate line above for dotted line divider when block border crosses a page
		// elseif ($divider == 'pagetop' || $continuingpage) {

		if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') {
			$tbd = $this->blk[$blvl]['border_bottom'];
			if (isset($tbd['s']) && $tbd['s']) {
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('q');
					$this->SetLineWidth(0);
					$this->_out(sprintf('%.3F %.3F m ', ($x0) * Mpdf::SCALE, ($this->h - ($y0 + $h)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $border_left) * Mpdf::SCALE, ($this->h - ($y0 + $h - $border_bottom)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $w - $border_right) * Mpdf::SCALE, ($this->h - ($y0 + $h - $border_bottom)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $w) * Mpdf::SCALE, ($this->h - ($y0 + $h)) * Mpdf::SCALE));
					$this->_out(' h W n '); // Ends path no-op & Sets the clipping path
				}

				$this->_setBorderLine($tbd);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$this->_setDashBorder($tbd['style'], $divider, $continuingpage, 'B');
				} /* -- BORDER-RADIUS -- */ elseif (($brBL_V && $brBL_H) || ($brBR_V && $brBR_H) || $tbd['style'] == 'solid' || $tbd['style'] == 'double') {
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
				}
				$s = '';
				if ($brBL_H && $brBL_V) {
					$s .= ($this->_EllipseArc($x0 + $brBL_H, $y0 + $h - $brBL_V, $brBL_H - $border_bottom / 2, $brBL_V - $border_bottom / 2, 3, 2, true)) . "\n";
				} else { 				/* -- END BORDER-RADIUS -- */
					if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F m ', ($x0) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_bottom / 2))) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F m ', ($x0 + ($border_bottom / 2)) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_bottom / 2))) * Mpdf::SCALE)) . "\n";
					}
				}
				/* -- BORDER-RADIUS -- */
				if ($brBR_H && $brBR_V) {
					$s .= (sprintf('%.3F %.3F l ', ($x0 + $w - ($border_bottom / 2) - $brBR_H ) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_bottom / 2))) * Mpdf::SCALE)) . "\n";
					$s .= ($this->_EllipseArc($x0 + $w - $brBR_H, $y0 + $h - $brBR_V, $brBR_H - $border_bottom / 2, $brBR_V - $border_bottom / 2, 4, 1)) . "\n";
				} else { 				/* -- END BORDER-RADIUS -- */
					if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + $w) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_bottom / 2))) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + $w - ($border_bottom / 2)) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_bottom / 2))) * Mpdf::SCALE)) . "\n";
					}
				}
				$s .= 'S' . "\n";
				$this->_out($s);

				if ($tbd['style'] == 'double') {
					$this->SetLineWidth($tbd['w'] / 3);
					$this->SetDColor($tbcol);
					$this->_out($s);
				}
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('Q');
				}

				// Reset Corners and Dash off
				$this->SetLineWidth(0.1);
				$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}
		// Reinstate line below for dotted line divider when block border crosses a page
		// elseif ($blockstate == 1 || $divider == 'pagebottom') {

		if ($this->blk[$blvl]['border_left']) {
			$tbd = $this->blk[$blvl]['border_left'];
			if (isset($tbd['s']) && $tbd['s']) {
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('q');
					$this->SetLineWidth(0);
					$this->_out(sprintf('%.3F %.3F m ', ($x0) * Mpdf::SCALE, ($this->h - ($y0)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $border_left) * Mpdf::SCALE, ($this->h - ($y0 + $border_top)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $border_left) * Mpdf::SCALE, ($this->h - ($y0 + $h - $border_bottom)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0) * Mpdf::SCALE, ($this->h - ($y0 + $h)) * Mpdf::SCALE));
					$this->_out(' h W n '); // Ends path no-op & Sets the clipping path
				}

				$this->_setBorderLine($tbd);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$this->_setDashBorder($tbd['style'], $divider, $continuingpage, 'L');
				} /* -- BORDER-RADIUS -- */ elseif (($brTL_V && $brTL_H) || ($brBL_V && $brBL_H) || $tbd['style'] == 'solid' || $tbd['style'] == 'double') {
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
				}
				$s = '';
				if ($brTL_V && $brTL_H) {
					$s .= ($this->_EllipseArc($x0 + $brTL_H, $y0 + $brTL_V, $brTL_H - $border_left / 2, $brTL_V - $border_left / 2, 2, 2, true)) . "\n";
				} else { 				/* -- END BORDER-RADIUS -- */
					if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F m ', ($x0 + ($border_left / 2)) * Mpdf::SCALE, ($this->h - ($y0)) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F m ', ($x0 + ($border_left / 2)) * Mpdf::SCALE, ($this->h - ($y0 + ($border_left / 2))) * Mpdf::SCALE)) . "\n";
					}
				}
				/* -- BORDER-RADIUS -- */
				if ($brBL_V && $brBL_H) {
					$s .= (sprintf('%.3F %.3F l ', ($x0 + ($border_left / 2)) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_left / 2) - $brBL_V) ) * Mpdf::SCALE)) . "\n";
					$s .= ($this->_EllipseArc($x0 + $brBL_H, $y0 + $h - $brBL_V, $brBL_H - $border_left / 2, $brBL_V - $border_left / 2, 3, 1)) . "\n";
				} else { 				/* -- END BORDER-RADIUS -- */
					if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + ($border_left / 2)) * Mpdf::SCALE, ($this->h - ($y0 + $h) ) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + ($border_left / 2)) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_left / 2)) ) * Mpdf::SCALE)) . "\n";
					}
				}
				$s .= 'S' . "\n";
				$this->_out($s);

				if ($tbd['style'] == 'double') {
					$this->SetLineWidth($tbd['w'] / 3);
					$this->SetDColor($tbcol);
					$this->_out($s);
				}
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('Q');
				}

				// Reset Corners and Dash off
				$this->SetLineWidth(0.1);
				$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}
		if ($this->blk[$blvl]['border_right']) {
			$tbd = $this->blk[$blvl]['border_right'];
			if (isset($tbd['s']) && $tbd['s']) {
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('q');
					$this->SetLineWidth(0);
					$this->_out(sprintf('%.3F %.3F m ', ($x0 + $w) * Mpdf::SCALE, ($this->h - ($y0)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $w - $border_right) * Mpdf::SCALE, ($this->h - ($y0 + $border_top)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $w - $border_right) * Mpdf::SCALE, ($this->h - ($y0 + $h - $border_bottom)) * Mpdf::SCALE));
					$this->_out(sprintf('%.3F %.3F l ', ($x0 + $w) * Mpdf::SCALE, ($this->h - ($y0 + $h)) * Mpdf::SCALE));
					$this->_out(' h W n '); // Ends path no-op & Sets the clipping path
				}

				$this->_setBorderLine($tbd);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$this->_setDashBorder($tbd['style'], $divider, $continuingpage, 'R');
				} /* -- BORDER-RADIUS -- */ elseif (($brTR_V && $brTR_H) || ($brBR_V && $brBR_H) || $tbd['style'] == 'solid' || $tbd['style'] == 'double') {
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
				}
				$s = '';
				if ($brBR_V && $brBR_H) {
					$s .= ($this->_EllipseArc($x0 + $w - $brBR_H, $y0 + $h - $brBR_V, $brBR_H - $border_right / 2, $brBR_V - $border_right / 2, 4, 2, true)) . "\n";
				} else { 				/* -- END BORDER-RADIUS -- */
					if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F m ', ($x0 + $w - ($border_right / 2)) * Mpdf::SCALE, ($this->h - ($y0 + $h)) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F m ', ($x0 + $w - ($border_right / 2)) * Mpdf::SCALE, ($this->h - ($y0 + $h - ($border_right / 2))) * Mpdf::SCALE)) . "\n";
					}
				}
				/* -- BORDER-RADIUS -- */
				if ($brTR_V && $brTR_H) {
					$s .= (sprintf('%.3F %.3F l ', ($x0 + $w - ($border_right / 2)) * Mpdf::SCALE, ($this->h - ($y0 + ($border_right / 2) + $brTR_V) ) * Mpdf::SCALE)) . "\n";
					$s .= ($this->_EllipseArc($x0 + $w - $brTR_H, $y0 + $brTR_V, $brTR_H - $border_right / 2, $brTR_V - $border_right / 2, 1, 1)) . "\n";
				} else { 				/* -- END BORDER-RADIUS -- */
					if ($tbd['style'] == 'solid' || $tbd['style'] == 'double') {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + $w - ($border_right / 2)) * Mpdf::SCALE, ($this->h - ($y0) ) * Mpdf::SCALE)) . "\n";
					} else {
						$s .= (sprintf('%.3F %.3F l ', ($x0 + $w - ($border_right / 2)) * Mpdf::SCALE, ($this->h - ($y0 + ($border_right / 2)) ) * Mpdf::SCALE)) . "\n";
					}
				}
				$s .= 'S' . "\n";
				$this->_out($s);

				if ($tbd['style'] == 'double') {
					$this->SetLineWidth($tbd['w'] / 3);
					$this->SetDColor($tbcol);
					$this->_out($s);
				}
				if (!$brset && $tbd['style'] != 'dotted' && $tbd['style'] != 'dashed') {
					$this->_out('Q');
				}

				// Reset Corners and Dash off
				$this->SetLineWidth(0.1);
				$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}


		$this->SetDash();
		$this->y = $save_y;


		// BACKGROUNDS are disabled in columns/kbt/headers - messes up the repositioning in printcolumnbuffer
		if ($this->ColActive || $this->kwt || $this->keep_block_together) {
			return;
		}


		$bgx0 = $x0;
		$bgx1 = $x1;
		$bgy0 = $y0;
		$bgy1 = $y1;

		// Defined br values represent the radius of the outer curve - need to take border-width/2 from each radius for drawing the borders
		if (isset($this->blk[$blvl]['background_clip']) && $this->blk[$blvl]['background_clip'] == 'padding-box') {
			$brbgTL_H = max(0, $brTL_H - $this->blk[$blvl]['border_left']['w']);
			$brbgTL_V = max(0, $brTL_V - $this->blk[$blvl]['border_top']['w']);
			$brbgTR_H = max(0, $brTR_H - $this->blk[$blvl]['border_right']['w']);
			$brbgTR_V = max(0, $brTR_V - $this->blk[$blvl]['border_top']['w']);
			$brbgBL_H = max(0, $brBL_H - $this->blk[$blvl]['border_left']['w']);
			$brbgBL_V = max(0, $brBL_V - $this->blk[$blvl]['border_bottom']['w']);
			$brbgBR_H = max(0, $brBR_H - $this->blk[$blvl]['border_right']['w']);
			$brbgBR_V = max(0, $brBR_V - $this->blk[$blvl]['border_bottom']['w']);
			$bgx0 += $this->blk[$blvl]['border_left']['w'];
			$bgx1 -= $this->blk[$blvl]['border_right']['w'];
			if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
				$bgy0 += $this->blk[$blvl]['border_top']['w'];
			}
			if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') {
				$bgy1 -= $this->blk[$blvl]['border_bottom']['w'];
			}
		} elseif (isset($this->blk[$blvl]['background_clip']) && $this->blk[$blvl]['background_clip'] == 'content-box') {
			$brbgTL_H = max(0, $brTL_H - $this->blk[$blvl]['border_left']['w'] - $this->blk[$blvl]['padding_left']);
			$brbgTL_V = max(0, $brTL_V - $this->blk[$blvl]['border_top']['w'] - $this->blk[$blvl]['padding_top']);
			$brbgTR_H = max(0, $brTR_H - $this->blk[$blvl]['border_right']['w'] - $this->blk[$blvl]['padding_right']);
			$brbgTR_V = max(0, $brTR_V - $this->blk[$blvl]['border_top']['w'] - $this->blk[$blvl]['padding_top']);
			$brbgBL_H = max(0, $brBL_H - $this->blk[$blvl]['border_left']['w'] - $this->blk[$blvl]['padding_left']);
			$brbgBL_V = max(0, $brBL_V - $this->blk[$blvl]['border_bottom']['w'] - $this->blk[$blvl]['padding_bottom']);
			$brbgBR_H = max(0, $brBR_H - $this->blk[$blvl]['border_right']['w'] - $this->blk[$blvl]['padding_right']);
			$brbgBR_V = max(0, $brBR_V - $this->blk[$blvl]['border_bottom']['w'] - $this->blk[$blvl]['padding_bottom']);
			$bgx0 += $this->blk[$blvl]['border_left']['w'] + $this->blk[$blvl]['padding_left'];
			$bgx1 -= $this->blk[$blvl]['border_right']['w'] + $this->blk[$blvl]['padding_right'];
			if (($this->blk[$blvl]['border_top']['w'] || $this->blk[$blvl]['padding_top']) && $divider != 'pagetop' && !$continuingpage) {
				$bgy0 += $this->blk[$blvl]['border_top']['w'] + $this->blk[$blvl]['padding_top'];
			}
			if (($this->blk[$blvl]['border_bottom']['w'] || $this->blk[$blvl]['padding_bottom']) && $blockstate != 1 && $divider != 'pagebottom') {
				$bgy1 -= $this->blk[$blvl]['border_bottom']['w'] + $this->blk[$blvl]['padding_bottom'];
			}
		} else {
			$brbgTL_H = $brTL_H;
			$brbgTL_V = $brTL_V;
			$brbgTR_H = $brTR_H;
			$brbgTR_V = $brTR_V;
			$brbgBL_H = $brBL_H;
			$brbgBL_V = $brBL_V;
			$brbgBR_H = $brBR_H;
			$brbgBR_V = $brBR_V;
		}

		// Set clipping path
		$s = ' q 0 w '; // Line width=0
		$s .= sprintf('%.3F %.3F m ', ($bgx0 + $brbgTL_H ) * Mpdf::SCALE, ($this->h - $bgy0) * Mpdf::SCALE); // start point TL before the arc
		/* -- BORDER-RADIUS -- */
		if ($brbgTL_H || $brbgTL_V) {
			$s .= $this->_EllipseArc($bgx0 + $brbgTL_H, $bgy0 + $brbgTL_V, $brbgTL_H, $brbgTL_V, 2); // segment 2 TL
		}
		/* -- END BORDER-RADIUS -- */
		$s .= sprintf('%.3F %.3F l ', ($bgx0) * Mpdf::SCALE, ($this->h - ($bgy1 - $brbgBL_V )) * Mpdf::SCALE); // line to BL
		/* -- BORDER-RADIUS -- */
		if ($brbgBL_H || $brbgBL_V) {
			$s .= $this->_EllipseArc($bgx0 + $brbgBL_H, $bgy1 - $brbgBL_V, $brbgBL_H, $brbgBL_V, 3); // segment 3 BL
		}
		/* -- END BORDER-RADIUS -- */
		$s .= sprintf('%.3F %.3F l ', ($bgx1 - $brbgBR_H ) * Mpdf::SCALE, ($this->h - ($bgy1)) * Mpdf::SCALE); // line to BR
		/* -- BORDER-RADIUS -- */
		if ($brbgBR_H || $brbgBR_V) {
			$s .= $this->_EllipseArc($bgx1 - $brbgBR_H, $bgy1 - $brbgBR_V, $brbgBR_H, $brbgBR_V, 4); // segment 4 BR
		}
		/* -- END BORDER-RADIUS -- */
		$s .= sprintf('%.3F %.3F l ', ($bgx1) * Mpdf::SCALE, ($this->h - ($bgy0 + $brbgTR_V)) * Mpdf::SCALE); // line to TR
		/* -- BORDER-RADIUS -- */
		if ($brbgTR_H || $brbgTR_V) {
			$s .= $this->_EllipseArc($bgx1 - $brbgTR_H, $bgy0 + $brbgTR_V, $brbgTR_H, $brbgTR_V, 1); // segment 1 TR
		}
		/* -- END BORDER-RADIUS -- */
		$s .= sprintf('%.3F %.3F l ', ($bgx0 + $brbgTL_H ) * Mpdf::SCALE, ($this->h - $bgy0) * Mpdf::SCALE); // line to TL
		// Box Shadow
		$shadow = '';
		if (isset($this->blk[$blvl]['box_shadow']) && $this->blk[$blvl]['box_shadow'] && $h > 0) {
			foreach ($this->blk[$blvl]['box_shadow'] as $sh) {
				// Colors
				if ($sh['col']{0} == 1) {
					$colspace = 'Gray';
					if ($sh['col']{2} == 1) {
						$col1 = '1' . $sh['col'][1] . '1' . $sh['col'][3];
					} else {
						$col1 = '1' . $sh['col'][1] . '1' . chr(100);
					}
					$col2 = '1' . $sh['col'][1] . '1' . chr(0);
				} elseif ($sh['col']{0} == 4) { // CMYK
					$colspace = 'CMYK';
					$col1 = '6' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . $sh['col'][4] . chr(100);
					$col2 = '6' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . $sh['col'][4] . chr(0);
				} elseif ($sh['col']{0} == 5) { // RGBa
					$colspace = 'RGB';
					$col1 = '5' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . $sh['col'][4];
					$col2 = '5' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . chr(0);
				} elseif ($sh['col']{0} == 6) { // CMYKa
					$colspace = 'CMYK';
					$col1 = '6' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . $sh['col'][4] . $sh['col'][5];
					$col2 = '6' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . $sh['col'][4] . chr(0);
				} else {
					$colspace = 'RGB';
					$col1 = '5' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . chr(100);
					$col2 = '5' . $sh['col'][1] . $sh['col'][2] . $sh['col'][3] . chr(0);
				}

				// Use clipping path as set above (and rectangle around page) to clip area outside box
				$shadow .= $s; // Use the clipping path with W*
				$shadow .= sprintf('0 %.3F m %.3F %.3F l ', $this->h * Mpdf::SCALE, $this->w * Mpdf::SCALE, $this->h * Mpdf::SCALE);
				$shadow .= sprintf('%.3F 0 l 0 0 l 0 %.3F l ', $this->w * Mpdf::SCALE, $this->h * Mpdf::SCALE);
				$shadow .= 'W n' . "\n";

				$sh['blur'] = abs($sh['blur']); // cannot have negative blur value
				// Ensure spread/blur do not make effective shadow width/height < 0
				// Could do more complex things but this just adjusts spread value
				if (-$sh['spread'] + $sh['blur'] / 2 > min($w / 2, $h / 2)) {
					$sh['spread'] = $sh['blur'] / 2 - min($w / 2, $h / 2) + 0.01;
				}
				// Shadow Offset
				if ($sh['x'] || $sh['y']) {
					$shadow .= sprintf(' q 1 0 0 1 %.4F %.4F cm', $sh['x'] * Mpdf::SCALE, -$sh['y'] * Mpdf::SCALE) . "\n";
				}

				// Set path for INNER shadow
				$shadow .= ' q 0 w ';
				$shadow .= $this->SetFColor($col1, true) . "\n";
				if ($col1{0} == 5 && ord($col1{4}) < 100) { // RGBa
					$shadow .= $this->SetAlpha(ord($col1{4}) / 100, 'Normal', true, 'F') . "\n";
				} elseif ($col1{0} == 6 && ord($col1{5}) < 100) { // CMYKa
					$shadow .= $this->SetAlpha(ord($col1{5}) / 100, 'Normal', true, 'F') . "\n";
				} elseif ($col1{0} == 1 && $col1{2} == 1 && ord($col1{3}) < 100) { // Gray
					$shadow .= $this->SetAlpha(ord($col1{3}) / 100, 'Normal', true, 'F') . "\n";
				}

				// Blur edges
				$mag = 0.551784; // Bezier Control magic number for 4-part spline for circle/ellipse
				$mag2 = 0.551784; // Bezier Control magic number to fill in edge of blurred rectangle
				$d1 = $sh['spread'] + $sh['blur'] / 2;
				$d2 = $sh['spread'] - $sh['blur'] / 2;
				$bl = $sh['blur'];
				$x00 = $x0 - $d1;
				$y00 = $y0 - $d1;
				$w00 = $w + $d1 * 2;
				$h00 = $h + $d1 * 2;

				// If any border-radius is greater width-negative spread(inner edge), ignore radii for shadow or screws up
				$flatten = false;
				if (max($brbgTR_H, $brbgTL_H, $brbgBR_H, $brbgBL_H) >= $w + $d2) {
					$flatten = true;
				}
				if (max($brbgTR_V, $brbgTL_V, $brbgBR_V, $brbgBL_V) >= $h + $d2) {
					$flatten = true;
				}


				// TOP RIGHT corner
				$p1x = $x00 + $w00 - $d1 - $brbgTR_H;
				$p1c2x = $p1x + ($d2 + $brbgTR_H) * $mag;
				$p1y = $y00 + $bl;
				$p2x = $x00 + $w00 - $d1 - $brbgTR_H;
				$p2c2x = $p2x + ($d1 + $brbgTR_H) * $mag;
				$p2y = $y00;
				$p2c1y = $p2y + $bl / 2;
				$p3x = $x00 + $w00;
				$p3c2x = $p3x - $bl / 2;
				$p3y = $y00 + $d1 + $brbgTR_V;
				$p3c1y = $p3y - ($d1 + $brbgTR_V) * $mag;
				$p4x = $x00 + $w00 - $bl;
				$p4y = $y00 + $d1 + $brbgTR_V;
				$p4c2y = $p4y - ($d2 + $brbgTR_V) * $mag;
				if (-$d2 > min($brbgTR_H, $brbgTR_V) || $flatten) {
					$p1x = $x00 + $w00 - $bl;
					$p1c2x = $p1x;
					$p2x = $x00 + $w00 - $bl;
					$p2c2x = $p2x + $bl * $mag2;
					$p3y = $y00 + $bl;
					$p3c1y = $p3y - $bl * $mag2;
					$p4y = $y00 + $bl;
					$p4c2y = $p4y;
				}

				$shadow .= sprintf('%.3F %.3F m ', ($p1x ) * Mpdf::SCALE, ($this->h - ($p1y )) * Mpdf::SCALE);
				$shadow .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($p1c2x) * Mpdf::SCALE, ($this->h - ($p1y)) * Mpdf::SCALE, ($p4x) * Mpdf::SCALE, ($this->h - ($p4c2y)) * Mpdf::SCALE, ($p4x) * Mpdf::SCALE, ($this->h - ($p4y)) * Mpdf::SCALE);
				$patch_array[0]['f'] = 0;
				$patch_array[0]['points'] = [$p1x, $p1y, $p1x, $p1y,
					$p2x, $p2c1y, $p2x, $p2y, $p2c2x, $p2y,
					$p3x, $p3c1y, $p3x, $p3y, $p3c2x, $p3y,
					$p4x, $p4y, $p4x, $p4y, $p4x, $p4c2y,
					$p1c2x, $p1y];
				$patch_array[0]['colors'] = [$col1, $col2, $col2, $col1];


				// RIGHT
				$p1x = $x00 + $w00; // control point only matches p3 preceding
				$p1y = $y00 + $d1 + $brbgTR_V;
				$p2x = $x00 + $w00 - $bl; // control point only matches p4 preceding
				$p2y = $y00 + $d1 + $brbgTR_V;
				$p3x = $x00 + $w00 - $bl;
				$p3y = $y00 + $h00 - $d1 - $brbgBR_V;
				$p4x = $x00 + $w00;
				$p4c1x = $p4x - $bl / 2;
				$p4y = $y00 + $h00 - $d1 - $brbgBR_V;
				if (-$d2 > min($brbgTR_H, $brbgTR_V) || $flatten) {
					$p1y = $y00 + $bl;
					$p2y = $y00 + $bl;
				}
				if (-$d2 > min($brbgBR_H, $brbgBR_V) || $flatten) {
					$p3y = $y00 + $h00 - $bl;
					$p4y = $y00 + $h00 - $bl;
				}

				$shadow .= sprintf('%.3F %.3F l ', ($p3x ) * Mpdf::SCALE, ($this->h - ($p3y )) * Mpdf::SCALE);
				$patch_array[1]['f'] = 2;
				$patch_array[1]['points'] = [$p2x, $p2y,
					$p3x, $p3y, $p3x, $p3y, $p3x, $p3y,
					$p4c1x, $p4y, $p4x, $p4y, $p4x, $p4y,
					$p1x, $p1y];
				$patch_array[1]['colors'] = [$col1, $col2];


				// BOTTOM RIGHT corner
				$p1x = $x00 + $w00 - $bl;  // control points only matches p3 preceding
				$p1y = $y00 + $h00 - $d1 - $brbgBR_V;
				$p1c2y = $p1y + ($d2 + $brbgBR_V) * $mag;
				$p2x = $x00 + $w00;     // control point only matches p4 preceding
				$p2y = $y00 + $h00 - $d1 - $brbgBR_V;
				$p2c2y = $p2y + ($d1 + $brbgBR_V) * $mag;
				$p3x = $x00 + $w00 - $d1 - $brbgBR_H;
				$p3c1x = $p3x + ($d1 + $brbgBR_H) * $mag;
				$p3y = $y00 + $h00;
				$p3c2y = $p3y - $bl / 2;
				$p4x = $x00 + $w00 - $d1 - $brbgBR_H;
				$p4c2x = $p4x + ($d2 + $brbgBR_H) * $mag;
				$p4y = $y00 + $h00 - $bl;

				if (-$d2 > min($brbgBR_H, $brbgBR_V) || $flatten) {
					$p1y = $y00 + $h00 - $bl;
					$p1c2y = $p1y;
					$p2y = $y00 + $h00 - $bl;
					$p2c2y = $p2y + $bl * $mag2;
					$p3x = $x00 + $w00 - $bl;
					$p3c1x = $p3x + $bl * $mag2;
					$p4x = $x00 + $w00 - $bl;
					$p4c2x = $p4x;
				}

				$shadow .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($p1x) * Mpdf::SCALE, ($this->h - ($p1c2y)) * Mpdf::SCALE, ($p4c2x) * Mpdf::SCALE, ($this->h - ($p4y)) * Mpdf::SCALE, ($p4x) * Mpdf::SCALE, ($this->h - ($p4y)) * Mpdf::SCALE);
				$patch_array[2]['f'] = 2;
				$patch_array[2]['points'] = [$p2x, $p2c2y,
					$p3c1x, $p3y, $p3x, $p3y, $p3x, $p3c2y,
					$p4x, $p4y, $p4x, $p4y, $p4c2x, $p4y,
					$p1x, $p1c2y];
				$patch_array[2]['colors'] = [$col2, $col1];



				// BOTTOM
				$p1x = $x00 + $w00 - $d1 - $brbgBR_H; // control point only matches p3 preceding
				$p1y = $y00 + $h00;
				$p2x = $x00 + $w00 - $d1 - $brbgBR_H; // control point only matches p4 preceding
				$p2y = $y00 + $h00 - $bl;
				$p3x = $x00 + $d1 + $brbgBL_H;
				$p3y = $y00 + $h00 - $bl;
				$p4x = $x00 + $d1 + $brbgBL_H;
				$p4y = $y00 + $h00;
				$p4c1y = $p4y - $bl / 2;

				if (-$d2 > min($brbgBR_H, $brbgBR_V) || $flatten) {
					$p1x = $x00 + $w00 - $bl;
					$p2x = $x00 + $w00 - $bl;
				}
				if (-$d2 > min($brbgBL_H, $brbgBL_V) || $flatten) {
					$p3x = $x00 + $bl;
					$p4x = $x00 + $bl;
				}

				$shadow .= sprintf('%.3F %.3F l ', ($p3x ) * Mpdf::SCALE, ($this->h - ($p3y )) * Mpdf::SCALE);
				$patch_array[3]['f'] = 2;
				$patch_array[3]['points'] = [$p2x, $p2y,
					$p3x, $p3y, $p3x, $p3y, $p3x, $p3y,
					$p4x, $p4c1y, $p4x, $p4y, $p4x, $p4y,
					$p1x, $p1y];
				$patch_array[3]['colors'] = [$col1, $col2];

				// BOTTOM LEFT corner
				$p1x = $x00 + $d1 + $brbgBL_H;
				$p1c2x = $p1x - ($d2 + $brbgBL_H) * $mag; // control points only matches p3 preceding
				$p1y = $y00 + $h00 - $bl;
				$p2x = $x00 + $d1 + $brbgBL_H;
				$p2c2x = $p2x - ($d1 + $brbgBL_H) * $mag; // control point only matches p4 preceding
				$p2y = $y00 + $h00;
				$p3x = $x00;
				$p3c2x = $p3x + $bl / 2;
				$p3y = $y00 + $h00 - $d1 - $brbgBL_V;
				$p3c1y = $p3y + ($d1 + $brbgBL_V) * $mag;
				$p4x = $x00 + $bl;
				$p4y = $y00 + $h00 - $d1 - $brbgBL_V;
				$p4c2y = $p4y + ($d2 + $brbgBL_V) * $mag;
				if (-$d2 > min($brbgBL_H, $brbgBL_V) || $flatten) {
					$p1x = $x00 + $bl;
					$p1c2x = $p1x;
					$p2x = $x00 + $bl;
					$p2c2x = $p2x - $bl * $mag2;
					$p3y = $y00 + $h00 - $bl;
					$p3c1y = $p3y + $bl * $mag2;
					$p4y = $y00 + $h00 - $bl;
					$p4c2y = $p4y;
				}

				$shadow .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($p1c2x) * Mpdf::SCALE, ($this->h - ($p1y)) * Mpdf::SCALE, ($p4x) * Mpdf::SCALE, ($this->h - ($p4c2y)) * Mpdf::SCALE, ($p4x) * Mpdf::SCALE, ($this->h - ($p4y)) * Mpdf::SCALE);
				$patch_array[4]['f'] = 2;
				$patch_array[4]['points'] = [$p2c2x, $p2y,
					$p3x, $p3c1y, $p3x, $p3y, $p3c2x, $p3y,
					$p4x, $p4y, $p4x, $p4y, $p4x, $p4c2y,
					$p1c2x, $p1y];
				$patch_array[4]['colors'] = [$col2, $col1];


				// LEFT - joins on the right (C3-C4 of previous): f = 2
				$p1x = $x00; // control point only matches p3 preceding
				$p1y = $y00 + $h00 - $d1 - $brbgBL_V;
				$p2x = $x00 + $bl; // control point only matches p4 preceding
				$p2y = $y00 + $h00 - $d1 - $brbgBL_V;
				$p3x = $x00 + $bl;
				$p3y = $y00 + $d1 + $brbgTL_V;
				$p4x = $x00;
				$p4c1x = $p4x + $bl / 2;
				$p4y = $y00 + $d1 + $brbgTL_V;
				if (-$d2 > min($brbgBL_H, $brbgBL_V) || $flatten) {
					$p1y = $y00 + $h00 - $bl;
					$p2y = $y00 + $h00 - $bl;
				}
				if (-$d2 > min($brbgTL_H, $brbgTL_V) || $flatten) {
					$p3y = $y00 + $bl;
					$p4y = $y00 + $bl;
				}

				$shadow .= sprintf('%.3F %.3F l ', ($p3x ) * Mpdf::SCALE, ($this->h - ($p3y )) * Mpdf::SCALE);
				$patch_array[5]['f'] = 2;
				$patch_array[5]['points'] = [$p2x, $p2y,
					$p3x, $p3y, $p3x, $p3y, $p3x, $p3y,
					$p4c1x, $p4y, $p4x, $p4y, $p4x, $p4y,
					$p1x, $p1y];
				$patch_array[5]['colors'] = [$col1, $col2];

				// TOP LEFT corner
				$p1x = $x00 + $bl;  // control points only matches p3 preceding
				$p1y = $y00 + $d1 + $brbgTL_V;
				$p1c2y = $p1y - ($d2 + $brbgTL_V) * $mag;
				$p2x = $x00;   // control point only matches p4 preceding
				$p2y = $y00 + $d1 + $brbgTL_V;
				$p2c2y = $p2y - ($d1 + $brbgTL_V) * $mag;
				$p3x = $x00 + $d1 + $brbgTL_H;
				$p3c1x = $p3x - ($d1 + $brbgTL_H) * $mag;
				$p3y = $y00;
				$p3c2y = $p3y + $bl / 2;
				$p4x = $x00 + $d1 + $brbgTL_H;
				$p4c2x = $p4x - ($d2 + $brbgTL_H) * $mag;
				$p4y = $y00 + $bl;

				if (-$d2 > min($brbgTL_H, $brbgTL_V) || $flatten) {
					$p1y = $y00 + $bl;
					$p1c2y = $p1y;
					$p2y = $y00 + $bl;
					$p2c2y = $p2y - $bl * $mag2;
					$p3x = $x00 + $bl;
					$p3c1x = $p3x - $bl * $mag2;
					$p4x = $x00 + $bl;
					$p4c2x = $p4x;
				}

				$shadow .= sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', ($p1x) * Mpdf::SCALE, ($this->h - ($p1c2y)) * Mpdf::SCALE, ($p4c2x) * Mpdf::SCALE, ($this->h - ($p4y)) * Mpdf::SCALE, ($p4x) * Mpdf::SCALE, ($this->h - ($p4y)) * Mpdf::SCALE);
				$patch_array[6]['f'] = 2;
				$patch_array[6]['points'] = [$p2x, $p2c2y,
					$p3c1x, $p3y, $p3x, $p3y, $p3x, $p3c2y,
					$p4x, $p4y, $p4x, $p4y, $p4c2x, $p4y,
					$p1x, $p1c2y];
				$patch_array[6]['colors'] = [$col2, $col1];


				// TOP - joins on the right (C3-C4 of previous): f = 2
				$p1x = $x00 + $d1 + $brbgTL_H; // control point only matches p3 preceding
				$p1y = $y00;
				$p2x = $x00 + $d1 + $brbgTL_H; // control point only matches p4 preceding
				$p2y = $y00 + $bl;
				$p3x = $x00 + $w00 - $d1 - $brbgTR_H;
				$p3y = $y00 + $bl;
				$p4x = $x00 + $w00 - $d1 - $brbgTR_H;
				$p4y = $y00;
				$p4c1y = $p4y + $bl / 2;
				if (-$d2 > min($brbgTL_H, $brbgTL_V) || $flatten) {
					$p1x = $x00 + $bl;
					$p2x = $x00 + $bl;
				}
				if (-$d2 > min($brbgTR_H, $brbgTR_V) || $flatten) {
					$p3x = $x00 + $w00 - $bl;
					$p4x = $x00 + $w00 - $bl;
				}

				$shadow .= sprintf('%.3F %.3F l ', ($p3x ) * Mpdf::SCALE, ($this->h - ($p3y )) * Mpdf::SCALE);
				$patch_array[7]['f'] = 2;
				$patch_array[7]['points'] = [$p2x, $p2y,
					$p3x, $p3y, $p3x, $p3y, $p3x, $p3y,
					$p4x, $p4c1y, $p4x, $p4y, $p4x, $p4y,
					$p1x, $p1y];
				$patch_array[7]['colors'] = [$col1, $col2];

				$shadow .= ' h f Q ' . "\n"; // Close path and Fill the inner solid shadow

				if ($bl) {
					$shadow .= $this->gradient->CoonsPatchMesh($x00, $y00, $w00, $h00, $patch_array, $x00, $x00 + $w00, $y00, $y00 + $h00, $colspace, true);
				}

				if ($sh['x'] || $sh['y']) {
					$shadow .= ' Q' . "\n";  // Shadow Offset
				}
				$shadow .= ' Q' . "\n"; // Ends path no-op & Sets the clipping path
			}
		}

		$s .= ' W n '; // Ends path no-op & Sets the clipping path

		if ($this->blk[$blvl]['bgcolor']) {
			$this->pageBackgrounds[$blvl][] = ['x' => $x0, 'y' => $y0, 'w' => $w, 'h' => $h, 'col' => $this->blk[$blvl]['bgcolorarray'], 'clippath' => $s, 'visibility' => $this->visibility, 'shadow' => $shadow, 'z-index' => $this->current_layer];
		} elseif ($shadow) {
			$this->pageBackgrounds[$blvl][] = ['shadowonly' => true, 'col' => '', 'clippath' => '', 'visibility' => $this->visibility, 'shadow' => $shadow, 'z-index' => $this->current_layer];
		}

		/* -- BACKGROUNDS -- */
		if (isset($this->blk[$blvl]['gradient'])) {
			$g = $this->gradient->parseBackgroundGradient($this->blk[$blvl]['gradient']);
			if ($g) {
				$gx = $x0;
				$gy = $y0;
				$this->pageBackgrounds[$blvl][] = ['gradient' => true, 'x' => $gx, 'y' => $gy, 'w' => $w, 'h' => $h, 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => $s, 'visibility' => $this->visibility, 'z-index' => $this->current_layer];
			}
		}
		if (isset($this->blk[$blvl]['background-image'])) {
			if (isset($this->blk[$blvl]['background-image']['gradient']) && $this->blk[$blvl]['background-image']['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $this->blk[$blvl]['background-image']['gradient'])) {
				$g = $this->gradient->parseMozGradient($this->blk[$blvl]['background-image']['gradient']);
				if ($g) {
					$gx = $x0;
					$gy = $y0;
					// origin specifies the background-positioning-area (bpa)
					if ($this->blk[$blvl]['background-image']['origin'] == 'padding-box') {
						$gx += $this->blk[$blvl]['border_left']['w'];
						$w -= ($this->blk[$blvl]['border_left']['w'] + $this->blk[$blvl]['border_right']['w']);
						if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
							$gy += $this->blk[$blvl]['border_top']['w'];
						}
						if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') {
							$gy1 = $y1 - $this->blk[$blvl]['border_bottom']['w'];
						} else {
							$gy1 = $y1;
						}
						$h = $gy1 - $gy;
					} elseif ($this->blk[$blvl]['background-image']['origin'] == 'content-box') {
						$gx += $this->blk[$blvl]['border_left']['w'] + $this->blk[$blvl]['padding_left'];
						$w -= ($this->blk[$blvl]['border_left']['w'] + $this->blk[$blvl]['padding_left'] + $this->blk[$blvl]['border_right']['w'] + $this->blk[$blvl]['padding_right']);
						if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
							$gy += $this->blk[$blvl]['border_top']['w'] + $this->blk[$blvl]['padding_top'];
						}
						if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') {
							$gy1 = $y1 - ($this->blk[$blvl]['border_bottom']['w'] + $this->blk[$blvl]['padding_bottom']);
						} else {
							$gy1 = $y1 - $this->blk[$blvl]['padding_bottom'];
						}
						$h = $gy1 - $gy;
					}

					if (isset($this->blk[$blvl]['background-image']['size']['w']) && $this->blk[$blvl]['background-image']['size']['w']) {
						$size = $this->blk[$blvl]['background-image']['size'];
						if ($size['w'] != 'contain' && $size['w'] != 'cover') {
							if (stristr($size['w'], '%')) {
								$size['w'] = (float) $size['w'];
								$size['w'] /= 100;
								$w *= $size['w'];
							} elseif ($size['w'] != 'auto') {
								$w = $size['w'];
							}
							if (stristr($size['h'], '%')) {
								$size['h'] = (float) $size['h'];
								$size['h'] /= 100;
								$h *= $size['h'];
							} elseif ($size['h'] != 'auto') {
								$h = $size['h'];
							}
						}
					}
					$this->pageBackgrounds[$blvl][] = ['gradient' => true, 'x' => $gx, 'y' => $gy, 'w' => $w, 'h' => $h, 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => $s, 'visibility' => $this->visibility, 'z-index' => $this->current_layer];
				}
			} else {
				$image_id = $this->blk[$blvl]['background-image']['image_id'];
				$orig_w = $this->blk[$blvl]['background-image']['orig_w'];
				$orig_h = $this->blk[$blvl]['background-image']['orig_h'];
				$x_pos = $this->blk[$blvl]['background-image']['x_pos'];
				$y_pos = $this->blk[$blvl]['background-image']['y_pos'];
				$x_repeat = $this->blk[$blvl]['background-image']['x_repeat'];
				$y_repeat = $this->blk[$blvl]['background-image']['y_repeat'];
				$resize = $this->blk[$blvl]['background-image']['resize'];
				$opacity = $this->blk[$blvl]['background-image']['opacity'];
				$itype = $this->blk[$blvl]['background-image']['itype'];
				$size = $this->blk[$blvl]['background-image']['size'];
				// origin specifies the background-positioning-area (bpa)
				$bpa = ['x' => $x0, 'y' => $y0, 'w' => $w, 'h' => $h];
				if ($this->blk[$blvl]['background-image']['origin'] == 'padding-box') {
					$bpa['x'] = $x0 + $this->blk[$blvl]['border_left']['w'];
					$bpa['w'] = $w - ($this->blk[$blvl]['border_left']['w'] + $this->blk[$blvl]['border_right']['w']);
					if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
						$bpa['y'] = $y0 + $this->blk[$blvl]['border_top']['w'];
					} else {
						$bpa['y'] = $y0;
					}
					if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') {
						$bpay = $y1 - $this->blk[$blvl]['border_bottom']['w'];
					} else {
						$bpay = $y1;
					}
					$bpa['h'] = $bpay - $bpa['y'];
				} elseif ($this->blk[$blvl]['background-image']['origin'] == 'content-box') {
					$bpa['x'] = $x0 + $this->blk[$blvl]['border_left']['w'] + $this->blk[$blvl]['padding_left'];
					$bpa['w'] = $w - ($this->blk[$blvl]['border_left']['w'] + $this->blk[$blvl]['padding_left'] + $this->blk[$blvl]['border_right']['w'] + $this->blk[$blvl]['padding_right']);
					if ($this->blk[$blvl]['border_top'] && $divider != 'pagetop' && !$continuingpage) {
						$bpa['y'] = $y0 + $this->blk[$blvl]['border_top']['w'] + $this->blk[$blvl]['padding_top'];
					} else {
						$bpa['y'] = $y0 + $this->blk[$blvl]['padding_top'];
					}
					if ($this->blk[$blvl]['border_bottom'] && $blockstate != 1 && $divider != 'pagebottom') {
						$bpay = $y1 - ($this->blk[$blvl]['border_bottom']['w'] + $this->blk[$blvl]['padding_bottom']);
					} else {
						$bpay = $y1 - $this->blk[$blvl]['padding_bottom'];
					}
					$bpa['h'] = $bpay - $bpa['y'];
				}
				$this->pageBackgrounds[$blvl][] = ['x' => $x0, 'y' => $y0, 'w' => $w, 'h' => $h, 'image_id' => $image_id, 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $x_pos, 'y_pos' => $y_pos, 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'clippath' => $s, 'resize' => $resize, 'opacity' => $opacity, 'itype' => $itype, 'visibility' => $this->visibility, 'z-index' => $this->current_layer, 'size' => $size, 'bpa' => $bpa];
			}
		}
		/* -- END BACKGROUNDS -- */

		// Float DIV
		$this->blk[$blvl]['bb_painted'][$this->page] = true;
	}

	function PaintDivLnBorder($state = 0, $blvl = 0, $h = 0)
	{
		// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
		$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y + $h;

		$save_y = $this->y;

		$w = $this->blk[$blvl]['width'];
		$x0 = $this->x;    // left
		$y0 = $this->y;    // top
		$x1 = $this->x + $w;   // bottom
		$y1 = $this->y + $h;   // bottom

		if ($this->blk[$blvl]['border_top'] && ($state == 1 || $state == 3)) {
			$tbd = $this->blk[$blvl]['border_top'];
			if (isset($tbd['s']) && $tbd['s']) {
				$this->_setBorderLine($tbd);
				$this->y = $y0 + ($tbd['w'] / 2);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$this->_setDashBorder($tbd['style'], '', $continuingpage, 'T');
					$this->Line($x0 + ($tbd['w'] / 2), $this->y, $x0 + $w - ($tbd['w'] / 2), $this->y);
				} else {
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
					$this->Line($x0, $this->y, $x0 + $w, $this->y);
				}
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}
		if ($this->blk[$blvl]['border_left']) {
			$tbd = $this->blk[$blvl]['border_left'];
			if (isset($tbd['s']) && $tbd['s']) {
				$this->_setBorderLine($tbd);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$this->y = $y0 + ($tbd['w'] / 2);
					$this->_setDashBorder($tbd['style'], '', $continuingpage, 'L');
					$this->Line($x0 + ($tbd['w'] / 2), $this->y, $x0 + ($tbd['w'] / 2), $y0 + $h - ($tbd['w'] / 2));
				} else {
					$this->y = $y0;
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
					$this->Line($x0 + ($tbd['w'] / 2), $this->y, $x0 + ($tbd['w'] / 2), $y0 + $h);
				}
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}
		if ($this->blk[$blvl]['border_right']) {
			$tbd = $this->blk[$blvl]['border_right'];
			if (isset($tbd['s']) && $tbd['s']) {
				$this->_setBorderLine($tbd);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$this->y = $y0 + ($tbd['w'] / 2);
					$this->_setDashBorder($tbd['style'], '', $continuingpage, 'R');
					$this->Line($x0 + $w - ($tbd['w'] / 2), $this->y, $x0 + $w - ($tbd['w'] / 2), $y0 + $h - ($tbd['w'] / 2));
				} else {
					$this->y = $y0;
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
					$this->Line($x0 + $w - ($tbd['w'] / 2), $this->y, $x0 + $w - ($tbd['w'] / 2), $y0 + $h);
				}
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}
		if ($this->blk[$blvl]['border_bottom'] && $state > 1) {
			$tbd = $this->blk[$blvl]['border_bottom'];
			if (isset($tbd['s']) && $tbd['s']) {
				$this->_setBorderLine($tbd);
				$this->y = $y0 + $h - ($tbd['w'] / 2);
				if ($tbd['style'] == 'dotted' || $tbd['style'] == 'dashed') {
					$this->_setDashBorder($tbd['style'], '', $continuingpage, 'B');
					$this->Line($x0 + ($tbd['w'] / 2), $this->y, $x0 + $w - ($tbd['w'] / 2), $this->y);
				} else {
					$this->SetLineJoin(0);
					$this->SetLineCap(0);
					$this->Line($x0, $this->y, $x0 + $w, $this->y);
				}
				$this->y += $tbd['w'];
				// Reset Corners and Dash off
				$this->SetLineJoin(2);
				$this->SetLineCap(2);
				$this->SetDash();
			}
		}
		$this->SetDash();
		$this->y = $save_y;
	}

	function restoreFont(&$saved, $write = true){
		if (!isset($saved) || empty($saved)) {
			return;
		}

		$this->FontFamily = $saved['family'];
		$this->FontStyle = $saved['style'];
		$this->FontSizePt = $saved['sizePt'];
		$this->FontSize = $saved['size'];
		$this->CurrentFont = &$saved['curr'];
		$this->currentLang = $saved['lang']; // mPDF 6
		$this->TextColor = $saved['color'];
		$this->spanbgcolor = $saved['spanbgcolor'];
		$this->spanbgcolorarray = $saved['spanbgcolorarray'];
		$this->spanborder = $saved['bord'];
		$this->spanborddet = $saved['border'];
		$this->ColorFlag = ($this->FillColor != $this->TextColor); // Restore ColorFlag as well
		$this->HREF = $saved['HREF'];
		$this->fixedlSpacing = $saved['fixedlSpacing'];
		$this->minwSpacing = $saved['minwSpacing'];
		$this->textvar = $saved['textvar'];  // mPDF 5.7.1
		$this->textshadow = $saved['textshadow'];
		$this->LineWidth = $saved['linewidth'];
		$this->DrawColor = $saved['drawcolor'];
		$this->textparam = $saved['textparam'];
		if ($write) {
			$this->SetFont($saved['family'], $saved['style'], $saved['sizePt'], true, true); // force output
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
				$this->_out($fontout);
			}
			$this->pageoutput[$this->page]['Font'] = $fontout;
		} else {
			$this->SetFont($saved['family'], $saved['style'], $saved['sizePt'], false);
		}
		$this->ReqFontStyle = $saved['ReqFontStyle'];
	}

	function DivLn($h, $level = -3, $move_y = true, $collapsible = false, $state = 0)
	{
		// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
		// Used in Columns and keep-with-table i.e. "kwt"
		// writes background block by block so it can be repositioned
		// and also used in writingFlowingBlock at top and bottom of blocks to move y (not to draw/paint anything)
		// adds lines (y) where DIV bgcolors are filled in
		// this->x is returned as it was
		// allows .00001 as nominal height used for bookmarks/annotations etc.
		if ($collapsible && (sprintf("%0.4f", $this->y) == sprintf("%0.4f", $this->tMargin)) && (!$this->ColActive)) {
			return;
		}

		// mPDF 6 Columns
		//   if ($collapsible && (sprintf("%0.4f", $this->y)==sprintf("%0.4f", $this->y0)) && ($this->ColActive) && $this->CurrCol == 0) { return; }	// *COLUMNS*
		if ($collapsible && (sprintf("%0.4f", $this->y) == sprintf("%0.4f", $this->y0)) && ($this->ColActive)) {
			return;
		} // *COLUMNS*
		// Still use this method if columns or keep-with-table, as it allows repositioning later
		// otherwise, now uses PaintDivBB()
		if (!$this->ColActive && !$this->kwt) {
			if ($move_y && !$this->ColActive) {
				$this->y += $h;
			}
			return;
		}

		if ($level == -3) {
			$level = $this->blklvl;
		}
		$firstblockfill = $this->GetFirstBlockFill();
		if ($firstblockfill && $this->blklvl > 0 && $this->blklvl >= $firstblockfill) {
			$last_x = 0;
			$last_w = 0;
			$last_fc = $this->FillColor;
			$bak_x = $this->x;
			$bak_h = $this->divheight;
			$this->divheight = 0; // Temporarily turn off divheight - as Cell() uses it to check for PageBreak
			for ($blvl = $firstblockfill; $blvl <= $level; $blvl++) {
				$this->x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
				// mPDF 6
				if ($this->blk[$blvl]['bgcolor']) {
					$this->SetFColor($this->blk[$blvl]['bgcolorarray']);
				}
				if ($last_x != ($this->lMargin + $this->blk[$blvl]['outer_left_margin']) || ($last_w != $this->blk[$blvl]['width']) || $last_fc != $this->FillColor || (isset($this->blk[$blvl]['border_top']['s']) && $this->blk[$blvl]['border_top']['s']) || (isset($this->blk[$blvl]['border_bottom']['s']) && $this->blk[$blvl]['border_bottom']['s']) || (isset($this->blk[$blvl]['border_left']['s']) && $this->blk[$blvl]['border_left']['s']) || (isset($this->blk[$blvl]['border_right']['s']) && $this->blk[$blvl]['border_right']['s'])) {
					$x = $this->x;
					$this->Cell(($this->blk[$blvl]['width']), $h, '', '', 0, '', 1);
					$this->x = $x;
					if (!$this->keep_block_together && !$this->writingHTMLheader && !$this->writingHTMLfooter) {
						// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
						if ($blvl == $this->blklvl) {
							$this->PaintDivLnBorder($state, $blvl, $h);
						} else {
							$this->PaintDivLnBorder(0, $blvl, $h);
						}
					}
				}
				$last_x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
				$last_w = $this->blk[$blvl]['width'];
				$last_fc = $this->FillColor;
			}
			// Reset current block fill
			if (isset($this->blk[$this->blklvl]['bgcolorarray'])) {
				$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
				$this->SetFColor($bcor);
			}
			$this->x = $bak_x;
			$this->divheight = $bak_h;
		}
		if ($move_y) {
			$this->y += $h;
		}
	}

	function finishFlowingBlock($endofblock = false, $next = '')
	{
		$currentx = $this->x;
		// prints out the last chunk
		$is_table = $this->flowingBlockAttr['is_table'];
		$table_draft = $this->flowingBlockAttr['table_draft'];
		$maxWidth = & $this->flowingBlockAttr['width'];
		$stackHeight = & $this->flowingBlockAttr['height'];
		$align = & $this->flowingBlockAttr['align'];
		$content = & $this->flowingBlockAttr['content'];
		$contentB = & $this->flowingBlockAttr['contentB'];
		$font = & $this->flowingBlockAttr['font'];
		$contentWidth = & $this->flowingBlockAttr['contentWidth'];
		$lineCount = & $this->flowingBlockAttr['lineCount'];
		$valign = & $this->flowingBlockAttr['valign'];
		$blockstate = $this->flowingBlockAttr['blockstate'];

		$cOTLdata = & $this->flowingBlockAttr['cOTLdata']; // mPDF 5.7.1
		$newblock = $this->flowingBlockAttr['newblock'];
		$blockdir = $this->flowingBlockAttr['blockdir'];

		// *********** BLOCK BACKGROUND COLOR *****************//
		if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
			$fill = 0;
		} else {
			$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
			$fill = 0;
		}

		$hanger = '';
		// Always right trim!
		// Right trim last content and adjust width if needed to justify (later)
		if (isset($content[count($content) - 1]) && preg_match('/[ ]+$/', $content[count($content) - 1], $m)) {
			$strip = strlen($m[0]);
			$content[count($content) - 1] = substr($content[count($content) - 1], 0, (strlen($content[count($content) - 1]) - $strip));
			/* -- OTL -- */
			if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
				$this->otl->trimOTLdata($cOTLdata[count($cOTLdata) - 1], false, true);
			}
			/* -- END OTL -- */
		}

		// the amount of space taken up so far in user units
		$usedWidth = 0;

		// COLS
		$oldcolumn = $this->CurrCol;

		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		} // *COLUMNS*
		// Print out each chunk

		/* -- TABLES -- */
		if ($is_table) {
			$ipaddingL = 0;
			$ipaddingR = 0;
			$paddingL = 0;
			$paddingR = 0;
		} else {
			/* -- END TABLES -- */
			$ipaddingL = $this->blk[$this->blklvl]['padding_left'];
			$ipaddingR = $this->blk[$this->blklvl]['padding_right'];
			$paddingL = ($ipaddingL * fpdf_protection::SCALE);
			$paddingR = ($ipaddingR * fpdf_protection::SCALE);
			$this->cMarginL = $this->blk[$this->blklvl]['border_left']['w'];
			$this->cMarginR = $this->blk[$this->blklvl]['border_right']['w'];

			// Added mPDF 3.0 Float DIV
			$fpaddingR = 0;
			$fpaddingL = 0;
			/* -- CSS-FLOAT -- */
			if (count($this->floatDivs)) {
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
				if ($r_exists) {
					$fpaddingR = $r_width;
				}
				if ($l_exists) {
					$fpaddingL = $l_width;
				}
			}
			/* -- END CSS-FLOAT -- */

			$usey = $this->y + 0.002;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
				$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
			}
			/* -- CSS-IMAGE-FLOAT -- */
			// If float exists at this level
			if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
				$fpaddingR += $this->floatmargins['R']['w'];
			}
			if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
				$fpaddingL += $this->floatmargins['L']['w'];
			}
			/* -- END CSS-IMAGE-FLOAT -- */
		} // *TABLES*


		$lineBox = [];

		$this->_setInlineBlockHeights($lineBox, $stackHeight, $content, $font, $is_table);

		if ($is_table && count($content) == 0) {
			$stackHeight = 0;
		}

		if ($table_draft) {
			$this->y += $stackHeight;
			$this->objectbuffer = [];
			return 0;
		}

		// While we're at it, check if contains cursive text
		// Change NBSP to SPACE.
		// Re-calculate contentWidth
		$contentWidth = 0;

		foreach ($content as $k => $chunk) {
			$this->restoreFont($font[$k], false);
			if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
				// Soft Hyphens chr(173)
				if (!$this->usingCoreFont) {
					/* -- OTL -- */
					// mPDF 5.7.1
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
						$this->otl->removeChar($chunk, $cOTLdata[$k], "\xc2\xad");
						$this->otl->replaceSpace($chunk, $cOTLdata[$k]);
						$content[$k] = $chunk;
					} /* -- END OTL -- */ else {  // *OTL*
						$content[$k] = $chunk = str_replace("\xc2\xad", '', $chunk);
						$content[$k] = $chunk = str_replace(chr(194) . chr(160), chr(32), $chunk);
					} // *OTL*
				} elseif ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
					$content[$k] = $chunk = str_replace(chr(173), '', $chunk);
					$content[$k] = $chunk = str_replace(chr(160), chr(32), $chunk);
				}
				$contentWidth += $this->GetStringWidth($chunk, true, (isset($cOTLdata[$k]) ? $cOTLdata[$k] : false), $this->textvar) * fpdf_protection::SCALE;
			} elseif (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
				// LIST MARKERS	// mPDF 6  Lists
				if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker'] && $this->objectbuffer[$k]['listmarkerposition'] == 'outside') {
					// do nothing
				} else {
					$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * fpdf_protection::SCALE;
				}
			}
		}

		if (isset($font[count($font) - 1])) {
			$lastfontreqstyle = (isset($font[count($font) - 1]['ReqFontStyle']) ? $font[count($font) - 1]['ReqFontStyle'] : '');
			$lastfontstyle = (isset($font[count($font) - 1]['style']) ? $font[count($font) - 1]['style'] : '');
		} else {
			$lastfontreqstyle = null;
			$lastfontstyle = null;
		}
		if ($blockdir == 'ltr' && strpos($lastfontreqstyle, "I") !== false && strpos($lastfontstyle, "I") === false) { // Artificial italic
			$lastitalic = $this->FontSize * 0.15 * fpdf_protection::SCALE;
		} else {
			$lastitalic = 0;
		}

		// Get PAGEBREAK TO TEST for height including the bottom border/padding
		$check_h = max($this->divheight, $stackHeight);

		// This fixes a proven bug...
		if ($endofblock && $newblock && $blockstate == 0 && !$content) {
			$check_h = 0;
		}
		// but ? needs to fix potentially more widespread...
		// if (!$content) {  $check_h = 0; }

		if ($this->blklvl > 0 && !$is_table) {
			if ($endofblock && $blockstate > 1) {
				if ($this->blk[$this->blklvl]['page_break_after_avoid']) {
					$check_h += $stackHeight;
				}
				$check_h += ($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
			}
			if (($newblock && ($blockstate == 1 || $blockstate == 3) && $lineCount == 0) || ($endofblock && $blockstate == 3 && $lineCount == 0)) {
				$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
			}
		}

		// Force PAGE break if column height cannot take check-height
		if ($this->ColActive && $check_h > ($this->PageBreakTrigger - $this->y0)) {
			$this->SetCol($this->NbCol - 1);
		}

		// Avoid just border/background-color moved on to next page
		if ($endofblock && $blockstate > 1 && !$content) {
			$buff = $this->margBuffer;
		} else {
			$buff = 0;
		}


		// PAGEBREAK
		if (!$is_table && ($this->y + $check_h) > ($this->PageBreakTrigger + $buff) and ! $this->InFooter and $this->AcceptPageBreak()) {
			$bak_x = $this->x; // Current X position
			// WORD SPACING
			$ws = $this->ws; // Word Spacing
			$charspacing = $this->charspacing; // Character Spacing
			$this->ResetSpacing();

			$this->AddPage($this->CurOrientation);

			$this->x = $bak_x;
			// Added to correct for OddEven Margins
			$currentx += $this->MarginCorrection;
			$this->x += $this->MarginCorrection;

			// WORD SPACING
			$this->SetSpacing($charspacing, $ws);
		}


		/* -- COLUMNS -- */
		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			$oldcolumn = $this->CurrCol;
		}


		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		}
		/* -- END COLUMNS -- */

		// TOP MARGIN
		if ($newblock && ($blockstate == 1 || $blockstate == 3) && ($this->blk[$this->blklvl]['margin_top']) && $lineCount == 0 && !$is_table) {
			$this->DivLn($this->blk[$this->blklvl]['margin_top'], $this->blklvl - 1, true, $this->blk[$this->blklvl]['margin_collapse']);
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		if ($newblock && ($blockstate == 1 || $blockstate == 3) && $lineCount == 0 && !$is_table) {
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
			if ($this->blk[$this->blklvl]['float']) {
				$this->blk[$this->blklvl]['float_start_y'] = $this->y;
			}
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		// Paragraph INDENT
		$WidthCorrection = 0;
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && ($align != 'C')) {
			$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
			$WidthCorrection = ($ti * fpdf_protection::SCALE);
		}


		// PADDING and BORDER spacing/fill
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 0) && (!$is_table)) {
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'], -3, true, false, 1);
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
			$this->x = $currentx;
		}


		// Added mPDF 3.0 Float DIV
		$fpaddingR = 0;
		$fpaddingL = 0;
		/* -- CSS-FLOAT -- */
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if ($r_exists) {
				$fpaddingR = $r_width;
			}
			if ($l_exists) {
				$fpaddingL = $l_width;
			}
		}
		/* -- END CSS-FLOAT -- */

		$usey = $this->y + 0.002;
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
			$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		}
		/* -- CSS-IMAGE-FLOAT -- */
		// If float exists at this level
		if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
			$fpaddingR += $this->floatmargins['R']['w'];
		}
		if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
			$fpaddingL += $this->floatmargins['L']['w'];
		}
		/* -- END CSS-IMAGE-FLOAT -- */


		if ($content) {
			// In FinishFlowing Block no lines are justified as it is always last line
			// but if CJKorphan has allowed content width to go over max width, use J charspacing to compress line
			// JUSTIFICATION J - NOT!
			$nb_carac = 0;
			$nb_spaces = 0;
			$jcharspacing = 0;
			$jkashida = 0;
			$jws = 0;
			$inclCursive = false;
			$dottab = false;
			foreach ($content as $k => $chunk) {
				if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
					$nb_carac += mb_strlen($chunk, $this->mb_enc);
					$nb_spaces += mb_substr_count($chunk, ' ', $this->mb_enc);
					// mPDF 6
					// Use GPOS OTL
					$this->restoreFont($font[$k], false);
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
						if (isset($cOTLdata[$k]['group']) && $cOTLdata[$k]['group']) {
							$nb_marks = substr_count($cOTLdata[$k]['group'], 'M');
							$nb_carac -= $nb_marks;
						}
						if (preg_match("/([" . $this->pregCURSchars . "])/u", $chunk)) {
							$inclCursive = true;
						}
					}
				} else {
					$nb_carac ++;  // mPDF 6 allow spacing for inline object
					if ($this->objectbuffer[$k]['type'] == 'dottab') {
						$dottab = $this->objectbuffer[$k]['outdent'];
					}
				}
			}

			// DIRECTIONALITY RTL
			$chunkorder = range(0, count($content) - 1); // mPDF 6
			/* -- OTL -- */
			// mPDF 6
			if ($blockdir == 'rtl' || $this->biDirectional) {
				$this->otl->bidiReorder($chunkorder, $content, $cOTLdata, $blockdir);
				// From this point on, $content and $cOTLdata may contain more elements (and re-ordered) compared to
				// $this->objectbuffer and $font ($chunkorder contains the mapping)
			}
			/* -- END OTL -- */

			// Remove any XAdvance from OTL data at end of line
			// And correct for XPlacement on last character
			// BIDI is applied
			foreach ($chunkorder as $aord => $k) {
				if (count($cOTLdata)) {
					$this->restoreFont($font[$k], false);
					// ...FinishFlowingBlock...
					if ($aord == count($chunkorder) - 1 && isset($cOTLdata[$aord]['group'])) { // Last chunk on line
						$nGPOS = strlen($cOTLdata[$aord]['group']) - 1; // Last character
						if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL']) || isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'])) {
							if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'])) {
								$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
							} else {
								$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
							}
							$w *= ($this->FontSize / 1000);
							$contentWidth -= $w * fpdf_protection::SCALE;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = 0;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = 0;
						}

						// If last character has an XPlacement set, adjust width calculation, and add to XAdvance to account for it
						if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'])) {
							$w = -$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
							$w *= ($this->FontSize / 1000);
							$contentWidth -= $w * fpdf_protection::SCALE;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
						}
					}
				}
			}

			// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
			// If "orphans" in fact is just a final space - ignore this
			$lastchar = mb_substr($content[(count($chunkorder) - 1)], mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, 1, $this->mb_enc);
			if (preg_match("/[" . $this->CJKoverflow . "]/u", $lastchar)) {
				$CJKoverflow = true;
			} else {
				$CJKoverflow = false;
			}
			if ((((($contentWidth + $lastitalic) > $maxWidth) && ($content[(count($chunkorder) - 1)] != ' ') ) ||
				(!$endofblock && $align == 'J' && ($next == 'image' || $next == 'select' || $next == 'input' || $next == 'textarea' || ($next == 'br' && $this->justifyB4br)))) && !($CJKoverflow && $this->allowCJKoverflow)) {
				// WORD SPACING
				list($jcharspacing, $jws, $jkashida) = $this->GetJspacing($nb_carac, $nb_spaces, ($maxWidth - $lastitalic - $contentWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * fpdf_protection::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * fpdf_protection::SCALE) )), $inclCursive, $cOTLdata);
			} /* -- CJK-FONTS -- */ elseif ($this->checkCJK && $align == 'J' && $CJKoverflow && $this->allowCJKoverflow && $this->CJKforceend) {
				// force-end overhang
				$hanger = mb_substr($content[(count($chunkorder) - 1)], mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, 1, $this->mb_enc);
				if (preg_match("/[" . $this->CJKoverflow . "]/u", $hanger)) {
					$content[(count($chunkorder) - 1)] = mb_substr($content[(count($chunkorder) - 1)], 0, mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, $this->mb_enc);
					$this->restoreFont($font[$chunkorder[count($chunkorder) - 1]], false);
					$contentWidth -= $this->GetStringWidth($hanger) * fpdf_protection::SCALE;
					$nb_carac -= 1;
					list($jcharspacing, $jws, $jkashida) = $this->GetJspacing($nb_carac, $nb_spaces, ($maxWidth - $lastitalic - $contentWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * fpdf_protection::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * fpdf_protection::SCALE) )), $inclCursive, $cOTLdata);
				}
			} /* -- END CJK-FONTS -- */

			// Check if will fit at word/char spacing of previous line - if so continue it
			// but only allow a maximum of $this->jSmaxWordLast and $this->jSmaxCharLast
			elseif ($contentWidth < ($maxWidth - $lastitalic - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * fpdf_protection::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * fpdf_protection::SCALE))) && !$this->fixedlSpacing) {
				if ($this->ws > $this->jSmaxWordLast) {
					$jws = $this->jSmaxWordLast;
				}
				if ($this->charspacing > $this->jSmaxCharLast) {
					$jcharspacing = $this->jSmaxCharLast;
				}
				$check = $maxWidth - $lastitalic - $WidthCorrection - $contentWidth - (($this->cMarginL + $this->cMarginR) * fpdf_protection::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * fpdf_protection::SCALE) ) - ( $jcharspacing * $nb_carac) - ( $jws * $nb_spaces);
				if ($check <= 0) {
					$jcharspacing = 0;
					$jws = 0;
				}
			}

			$empty = $maxWidth - $lastitalic - $WidthCorrection - $contentWidth - (($this->cMarginL + $this->cMarginR) * fpdf_protection::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * fpdf_protection::SCALE) );


			$empty -= ($jcharspacing * ($nb_carac - 1)); // mPDF 6 nb_carac MINUS 1
			$empty -= ($jws * $nb_spaces);
			$empty -= ($jkashida);

			$empty /= fpdf_protection::SCALE;

			if (!$is_table) {
				$this->maxPosR = max($this->maxPosR, ($this->w - $this->rMargin - $this->blk[$this->blklvl]['outer_right_margin'] - $empty));
				$this->maxPosL = min($this->maxPosL, ($this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'] + $empty));
			}

			$arraysize = count($chunkorder);

			$margins = ($this->cMarginL + $this->cMarginR) + ($ipaddingL + $ipaddingR + $fpaddingR + $fpaddingR );

			if (!$is_table) {
				$this->DivLn($stackHeight, $this->blklvl, false);
			} // false -> don't advance y

			$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL;
			if ($dottab !== false && $blockdir == 'rtl') {
				$this->x -= $dottab;
			} elseif ($align == 'R') {
				$this->x += $empty;
			} elseif ($align == 'J' && $blockdir == 'rtl') {
				$this->x += $empty;
			} elseif ($align == 'C') {
				$this->x += ($empty / 2);
			}

			// Paragraph INDENT
			$WidthCorrection = 0;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && ($align != 'C')) {
				$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
				if ($blockdir != 'rtl') {
					$this->x += $ti;
				} // mPDF 6
			}

			foreach ($chunkorder as $aord => $k) { // mPDF 5.7
				$chunk = $content[$aord];
				if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
					$xadj = $this->x - $this->objectbuffer[$k]['OUTER-X'];
					$this->objectbuffer[$k]['OUTER-X'] += $xadj;
					$this->objectbuffer[$k]['BORDER-X'] += $xadj;
					$this->objectbuffer[$k]['INNER-X'] += $xadj;

					if ($this->objectbuffer[$k]['type'] == 'listmarker') {
						$this->objectbuffer[$k]['lineBox'] = $lineBox[-1]; // Block element details for glyph-origin
					}
					$yadj = $this->y - $this->objectbuffer[$k]['OUTER-Y'];
					if ($this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
						$this->objectbuffer[$k]['lineBox'] = $lineBox[$k]; // element details for glyph-origin
					}
					if ($this->objectbuffer[$k]['type'] != 'dottab') { // mPDF 6 DOTTAB
						$yadj += $lineBox[$k]['top'];
					}
					$this->objectbuffer[$k]['OUTER-Y'] += $yadj;
					$this->objectbuffer[$k]['BORDER-Y'] += $yadj;
					$this->objectbuffer[$k]['INNER-Y'] += $yadj;
				}

				$this->restoreFont($font[$k]);  // mPDF 5.7

				if ($is_table && substr($align, 0, 1) == 'D' && $aord == 0) {
					$dp = $this->decimal_align[substr($align, 0, 2)];
					$s = preg_split('/' . preg_quote($dp, '/') . '/', $content[0], 2);  // ? needs to be /u if not core
					$s0 = $this->GetStringWidth($s[0], false);
					$this->x += ($this->decimal_offset - $s0);
				}

				$this->SetSpacing(($this->fixedlSpacing * fpdf_protection::SCALE) + $jcharspacing, ($this->fixedlSpacing + $this->minwSpacing) * fpdf_protection::SCALE + $jws);
				$this->fixedlSpacing = false;
				$this->minwSpacing = 0;

				$save_vis = $this->visibility;
				if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->textparam['visibility'] != $this->visibility) {
					$this->SetVisibility($this->textparam['visibility']);
				}

				// *********** SPAN BACKGROUND COLOR ***************** //
				if (isset($this->spanbgcolor) && $this->spanbgcolor) {
					$cor = $this->spanbgcolorarray;
					$this->SetFColor($cor);
					$save_fill = $fill;
					$spanfill = 1;
					$fill = 1;
				}
				if (!empty($this->spanborddet)) {
					if (strpos($contentB[$k], 'L') !== false && isset($this->spanborddet['L'])) {
						$this->x += $this->spanborddet['L']['w'];
					}
					if (strpos($contentB[$k], 'L') === false) {
						$this->spanborddet['L']['s'] = $this->spanborddet['L']['w'] = 0;
					}
					if (strpos($contentB[$k], 'R') === false) {
						$this->spanborddet['R']['s'] = $this->spanborddet['R']['w'] = 0;
					}
				}
				// WORD SPACING
				// mPDF 5.7.1
				$stringWidth = $this->GetStringWidth($chunk, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar);
				$nch = mb_strlen($chunk, $this->mb_enc);
				// Use GPOS OTL
				if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
					if (isset($cOTLdata[$aord]['group']) && $cOTLdata[$aord]['group']) {
						$nch -= substr_count($cOTLdata[$aord]['group'], 'M');
					}
				}
				$stringWidth += ( $this->charspacing * $nch / fpdf_protection::SCALE );

				$stringWidth += ( $this->ws * mb_substr_count($chunk, ' ', $this->mb_enc) / fpdf_protection::SCALE );

				if (isset($this->objectbuffer[$k])) {
					if ($this->objectbuffer[$k]['type'] == 'dottab') {
						$this->objectbuffer[$k]['OUTER-WIDTH'] +=$empty;
						$this->objectbuffer[$k]['OUTER-WIDTH'] +=$this->objectbuffer[$k]['outdent'];
					}
					// LIST MARKERS	// mPDF 6  Lists
					if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker'] && $this->objectbuffer[$k]['listmarkerposition'] == 'outside') {
						// do nothing
					} else {
						$stringWidth = $this->objectbuffer[$k]['OUTER-WIDTH'];
					}
				}

				if ($stringWidth == 0) {
					$stringWidth = 0.000001;
				}
				if ($aord == $arraysize - 1) { // mPDF 5.7
					// mPDF 5.7.1
					if ($this->checkCJK && $CJKoverflow && $align == 'J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {
						// force-end overhang
						$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false));  // mPDF 5.7.1
						$this->Cell($this->GetStringWidth($hanger), $stackHeight, $hanger, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // mPDF 5.7.1
					} else {
						$this->Cell($stringWidth, $stackHeight, $chunk, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // mPDF 5.7.1
					}
				} else {
					$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // first or middle part	// mPDF 5.7.1
				}


				if (!empty($this->spanborddet)) {
					if (strpos($contentB[$k], 'R') !== false && $aord != $arraysize - 1) {
						$this->x += $this->spanborddet['R']['w'];
					}
				}
				// *********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR ***************** //
				if (isset($spanfill) && $spanfill) {
					$fill = $save_fill;
					$spanfill = 0;
					if ($fill) {
						$this->SetFColor($bcor);
					}
				}
				if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->visibility != $save_vis) {
					$this->SetVisibility($save_vis);
				}
			}

			$this->printobjectbuffer($is_table, $blockdir);
			$this->objectbuffer = [];
			$this->ResetSpacing();
		} // END IF CONTENT

		/* -- CSS-IMAGE-FLOAT -- */
		// Update values if set to skipline
		if ($this->floatmargins) {
			$this->_advanceFloatMargins();
		}


		if ($endofblock && $blockstate > 1) {
			// If float exists at this level
			if (isset($this->floatmargins['R']['y1'])) {
				$fry1 = $this->floatmargins['R']['y1'];
			} else {
				$fry1 = 0;
			}
			if (isset($this->floatmargins['L']['y1'])) {
				$fly1 = $this->floatmargins['L']['y1'];
			} else {
				$fly1 = 0;
			}
			if ($this->y < $fry1 || $this->y < $fly1) {
				$drop = max($fry1, $fly1) - $this->y;
				$this->DivLn($drop);
				$this->x = $currentx;
			}
		}
		/* -- END CSS-IMAGE-FLOAT -- */


		// PADDING and BORDER spacing/fill
		if ($endofblock && ($blockstate > 1) && ($this->blk[$this->blklvl]['padding_bottom'] || $this->blk[$this->blklvl]['border_bottom'] || $this->blk[$this->blklvl]['css_set_height']) && (!$is_table)) {
			// If CSS height set, extend bottom - if on same page as block started, and CSS HEIGHT > actual height,
			// and does not force pagebreak
			$extra = 0;
			if (isset($this->blk[$this->blklvl]['css_set_height']) && $this->blk[$this->blklvl]['css_set_height'] && $this->blk[$this->blklvl]['startpage'] == $this->page) {
				// predicted height
				$h1 = ($this->y - $this->blk[$this->blklvl]['y0']) + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'];
				if ($h1 < ($this->blk[$this->blklvl]['css_set_height'] + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['padding_top'])) {
					$extra = ($this->blk[$this->blklvl]['css_set_height'] + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['padding_top']) - $h1;
				}
				if ($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra > $this->PageBreakTrigger) {
					$extra = $this->PageBreakTrigger - ($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
				}
			}

			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra, -3, true, false, 2);
			$this->x = $currentx;

			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		// SET Bottom y1 of block (used for painting borders)
		if (($endofblock) && ($blockstate > 1) && (!$is_table)) {
			$this->blk[$this->blklvl]['y1'] = $this->y;
		}

		// BOTTOM MARGIN
		if (($endofblock) && ($blockstate > 1) && ($this->blk[$this->blklvl]['margin_bottom']) && (!$is_table)) {
			if ($this->y + $this->blk[$this->blklvl]['margin_bottom'] < $this->PageBreakTrigger and ! $this->InFooter) {
				$this->DivLn($this->blk[$this->blklvl]['margin_bottom'], $this->blklvl - 1, true, $this->blk[$this->blklvl]['margin_collapse']);
				if ($this->ColActive) {
					$this->breakpoints[$this->CurrCol][] = $this->y;
				} // *COLUMNS*
			}
		}

		// Reset lineheight
		$stackHeight = $this->divheight;
	}

	function printbuffer($arrayaux, $blockstate = 0, $is_table = false, $table_draft = false, $cell_dir = '')
	{
		// $blockstate = 0;	// NO margins/padding
		// $blockstate = 1;	// Top margins/padding only
		// $blockstate = 2;	// Bottom margins/padding only
		// $blockstate = 3;	// Top & bottom margins/padding
		$this->spanbgcolorarray = '';
		$this->spanbgcolor = false;
		$this->spanborder = false;
		$this->spanborddet = [];
		$paint_ht_corr = 0;
		/* -- CSS-FLOAT -- */
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if (($this->blk[$this->blklvl]['inner_width'] - $l_width - $r_width) < (2 * $this->GetCharWidth('W', false))) {
				// Too narrow to fit - try to move down past L or R float
				if ($l_max < $r_max && ($this->blk[$this->blklvl]['inner_width'] - $r_width) > (2 * $this->GetCharWidth('W', false))) {
					$this->ClearFloats('LEFT', $this->blklvl);
				} elseif ($r_max < $l_max && ($this->blk[$this->blklvl]['inner_width'] - $l_width) > (2 * $this->GetCharWidth('W', false))) {
					$this->ClearFloats('RIGHT', $this->blklvl);
				} else {
					$this->ClearFloats('BOTH', $this->blklvl);
				}
			}
		}
		/* -- END CSS-FLOAT -- */
		$bak_y = $this->y;
		$bak_x = $this->x;
		$align = '';
		if (!$is_table) {
			if (isset($this->blk[$this->blklvl]['align']) && $this->blk[$this->blklvl]['align']) {
				$align = $this->blk[$this->blklvl]['align'];
			}
			// Block-align is set by e.g. <.. align="center"> Takes priority for this block but not inherited
			if (isset($this->blk[$this->blklvl]['block-align']) && $this->blk[$this->blklvl]['block-align']) {
				$align = $this->blk[$this->blklvl]['block-align'];
			}
			if (isset($this->blk[$this->blklvl]['direction'])) {
				$blockdir = $this->blk[$this->blklvl]['direction'];
			} else {
				$blockdir = "";
			}
			$this->divwidth = $this->blk[$this->blklvl]['width'];
		} else {
			$align = $this->cellTextAlign;
			$blockdir = $cell_dir;
		}
		$oldpage = $this->page;

		// ADDED for Out of Block now done as Flowing Block
		if ($this->divwidth == 0) {
			$this->divwidth = $this->pgwidth;
		}

		if (!$is_table) {
			$this->SetLineHeight($this->FontSizePt, $this->blk[$this->blklvl]['line_height']);
		}
		$this->divheight = $this->lineheight;
		$old_height = $this->divheight;

		// As a failsafe - if font has been set but not output to page
		if (!$table_draft) {
			$this->SetFont($this->default_font, '', $this->default_font_size, true, true); // force output to page
		}

		$this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, true, $blockdir, $table_draft);

		$array_size = count($arrayaux);

		// Added - Otherwise <div><div><p> did not output top margins/padding for 1st/2nd div
		if ($array_size == 0) {
			$this->finishFlowingBlock(true);
		} // true = END of flowing block
		// mPDF 6
		// ALL the chunks of textbuffer need to have at least basic OTLdata set
		// First make sure each element/chunk has the OTLdata for Bidi set.
		for ($i = 0; $i < $array_size; $i++) {
			if (empty($arrayaux[$i][18])) {
				if (substr($arrayaux[$i][0], 0, 3) == "\xbb\xa4\xac") { // object identifier has been identified!
					$unicode = [0xFFFC]; // Object replacement character
				} else {
					$unicode = $this->UTF8StringToArray($arrayaux[$i][0], false);
				}
				$is_strong = false;
				$this->getBasicOTLdata($arrayaux[$i][18], $unicode, $is_strong);
			}
			// Gets messed up if try and use core fonts inside a paragraph of text which needs to be BiDi re-ordered or OTLdata set
			if (($blockdir == 'rtl' || $this->biDirectional) && isset($arrayaux[$i][4]) && in_array($arrayaux[$i][4], ['ccourier', 'ctimes', 'chelvetica', 'csymbol', 'czapfdingbats'])) {
				throw new MpdfException("You cannot use core fonts in a document which contains RTL text.");
			}
		}
		// mPDF 6
		// Process bidirectional text ready for bidi-re-ordering (which is done after line-breaks are established in WriteFlowingBlock etc.)
		if (($blockdir == 'rtl' || $this->biDirectional) && !$table_draft) {
			if (empty($this->otl)) {
				$this->otl = new Otl($this, $this->fontCache);
			}
			$this->otl->bidiPrepare($arrayaux, $blockdir);
			$array_size = count($arrayaux);
		}


		// Remove empty items // mPDF 6
		for ($i = $array_size - 1; $i > 0; $i--) {
			if (empty($arrayaux[$i][0]) && (isset($arrayaux[$i][16]) && $arrayaux[$i][16] !== '0') && empty($arrayaux[$i][7])) {
				unset($arrayaux[$i]);
			}
		}

		// Correct adjoining borders for inline elements
		if (isset($arrayaux[0][16])) {
			$lastspanborder = $arrayaux[0][16];
		} else {
			$lastspanborder = false;
		}
		for ($i = 1; $i < $array_size; $i++) {
			if (isset($arrayaux[$i][16]) && $arrayaux[$i][16] == $lastspanborder &&
				((!isset($arrayaux[$i][9]['bord-decoration']) && !isset($arrayaux[$i - 1][9]['bord-decoration'])) ||
				(isset($arrayaux[$i][9]['bord-decoration']) && isset($arrayaux[$i - 1][9]['bord-decoration']) && $arrayaux[$i][9]['bord-decoration'] == $arrayaux[$i - 1][9]['bord-decoration'])
				)
			) {
				if (isset($arrayaux[$i][16]['R'])) {
					$lastspanborder = $arrayaux[$i][16];
				} else {
					$lastspanborder = false;
				}
				$arrayaux[$i][16]['L']['s'] = 0;
				$arrayaux[$i][16]['L']['w'] = 0;
				$arrayaux[$i - 1][16]['R']['s'] = 0;
				$arrayaux[$i - 1][16]['R']['w'] = 0;
			} else {
				if (isset($arrayaux[$i][16]['R'])) {
					$lastspanborder = $arrayaux[$i][16];
				} else {
					$lastspanborder = false;
				}
			}
		}

		for ($i = 0; $i < $array_size; $i++) {
			// COLS
			$oldcolumn = $this->CurrCol;
			$vetor = isset($arrayaux[$i]) ? $arrayaux[$i] : null;
			if ($i == 0 && $vetor[0] != "\n" && ! $this->ispre) {
				$vetor[0] = ltrim($vetor[0]);
				if (!empty($vetor[18])) {
					$this->otl->trimOTLdata($vetor[18], true, false);
				} // *OTL*
			}

			// FIXED TO ALLOW IT TO SHOW '0'
			if (empty($vetor[0]) && !($vetor[0] === '0') && empty($vetor[7])) { // Ignore empty text and not carrying an internal link
				// Check if it is the last element. If so then finish printing the block
				if ($i == ($array_size - 1)) {
					$this->finishFlowingBlock(true);
				} // true = END of flowing block
				continue;
			}


			// Activating buffer properties
			if (isset($vetor[11]) && $vetor[11] != '') {   // Font Size
				if ($is_table && $this->shrin_k) {
					$this->SetFontSize($vetor[11] / $this->shrin_k, false);
				} else {
					$this->SetFontSize($vetor[11], false);
				}
			}

			if (isset($vetor[17]) && !empty($vetor[17])) { // TextShadow
				$this->textshadow = $vetor[17];
			}
			if (isset($vetor[16]) && !empty($vetor[16])) { // Border
				$this->spanborddet = $vetor[16];
				$this->spanborder = true;
			}

			if (isset($vetor[15])) {   // Word spacing
				$this->wSpacingCSS = $vetor[15];
				if ($this->wSpacingCSS && strtoupper($this->wSpacingCSS) != 'NORMAL') {
					$this->minwSpacing = $this->sizeConverter->convert($this->wSpacingCSS, $this->FontSize) / $this->shrin_k; // mPDF 5.7.3
				}
			}
			if (isset($vetor[14])) {   // Letter spacing
				$this->lSpacingCSS = $vetor[14];
				if (($this->lSpacingCSS || $this->lSpacingCSS === '0') && strtoupper($this->lSpacingCSS) != 'NORMAL') {
					$this->fixedlSpacing = $this->sizeConverter->convert($this->lSpacingCSS, $this->FontSize) / $this->shrin_k; // mPDF 5.7.3
				}
			}


			if (isset($vetor[10]) and ! empty($vetor[10])) { // Background color
				$this->spanbgcolorarray = $vetor[10];
				$this->spanbgcolor = true;
			}
			if (isset($vetor[9]) and ! empty($vetor[9])) { // Text parameters - Outline + hyphens
				$this->textparam = $vetor[9];
				$this->SetTextOutline($this->textparam);
				// mPDF 5.7.3  inline text-decoration parameters
				if ($is_table && $this->shrin_k) {
					if (isset($this->textparam['text-baseline'])) {
						$this->textparam['text-baseline'] /= $this->shrin_k;
					}
					if (isset($this->textparam['decoration-baseline'])) {
						$this->textparam['decoration-baseline'] /= $this->shrin_k;
					}
					if (isset($this->textparam['decoration-fontsize'])) {
						$this->textparam['decoration-fontsize'] /= $this->shrin_k;
					}
				}
			}
			if (isset($vetor[8])) {  // mPDF 5.7.1
				$this->textvar = $vetor[8];
			}
			if (isset($vetor[7]) and $vetor[7] != '') { // internal target: <a name="anyvalue">
				$ily = $this->y;
				if ($this->table_rotate) {
					$this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page, "tbrot" => true];
				} elseif ($this->kwt) {
					$this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page, "kwt" => true];
				} elseif ($this->ColActive) {
					$this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page, "col" => $this->CurrCol];
				} elseif (!$this->keep_block_together) {
					$this->internallink[$vetor[7]] = ["Y" => $ily, "PAGE" => $this->page];
				}
				if (empty($vetor[0])) { // Ignore empty text
					// Check if it is the last element. If so then finish printing the block
					if ($i == ($array_size - 1)) {
						$this->finishFlowingBlock(true);
					} // true = END of flowing block
					continue;
				}
			}
			if (isset($vetor[5]) and $vetor[5] != '') {  // Language	// mPDF 6
				$this->currentLang = $vetor[5];
			}
			if (isset($vetor[4]) and $vetor[4] != '') {  // Font Family
				$font = $this->SetFont($vetor[4], $this->FontStyle, 0, false);
			}
			if (!empty($vetor[3])) { // Font Color
				$cor = $vetor[3];
				$this->SetTColor($cor);
			}
			if (isset($vetor[2]) and $vetor[2] != '') { // Bold,Italic styles
				$this->SetStyles($vetor[2]);
			}

			if (isset($vetor[12]) and $vetor[12] != '') { // Requested Bold,Italic
				$this->ReqFontStyle = $vetor[12];
			}
			if (isset($vetor[1]) and $vetor[1] != '') { // LINK
				if (strpos($vetor[1], ".") === false && strpos($vetor[1], "@") !== 0) { // assuming every external link has a dot indicating extension (e.g: .html .txt .zip www.somewhere.com etc.)
					// Repeated reference to same anchor?
					while (array_key_exists($vetor[1], $this->internallink)) {
						$vetor[1] = "#" . $vetor[1];
					}
					$this->internallink[$vetor[1]] = $this->AddLink();
					$vetor[1] = $this->internallink[$vetor[1]];
				}
				$this->HREF = $vetor[1];     // HREF link style set here ******
			}

			// SPECIAL CONTENT - IMAGES & FORM OBJECTS
			// Print-out special content

			if (substr($vetor[0], 0, 3) == "\xbb\xa4\xac") { // identifier has been identified!
				$objattr = $this->_getObjAttr($vetor[0]);

				/* -- TABLES -- */
				if ($objattr['type'] == 'nestedtable') {
					if ($objattr['nestedcontent']) {
						$level = $objattr['level'];
						$table = &$this->table[$level][$objattr['table']];

						if ($table_draft) {
							$this->y += $this->table[($level + 1)][$objattr['nestedcontent']]['h']; // nested table height
							$this->finishFlowingBlock(false, 'nestedtable');
						} else {
							$cell = &$table['cells'][$objattr['row']][$objattr['col']];
							$this->finishFlowingBlock(false, 'nestedtable');
							$save_dw = $this->divwidth;
							$save_buffer = $this->cellBorderBuffer;
							$this->cellBorderBuffer = [];
							$ncx = $this->x;
							list($dummyx, $w) = $this->_tableGetWidth($table, $objattr['row'], $objattr['col']);
							$ntw = $this->table[($level + 1)][$objattr['nestedcontent']]['w']; // nested table width
							if (!$this->simpleTables) {
								if ($this->packTableData) {
									list($bt, $br, $bb, $bl) = $this->_getBorderWidths($cell['borderbin']);
								} else {
									$br = $cell['border_details']['R']['w'];
									$bl = $cell['border_details']['L']['w'];
								}
								if ($table['borders_separate']) {
									$innerw = $w - $bl - $br - $cell['padding']['L'] - $cell['padding']['R'] - $table['border_spacing_H'];
								} else {
									$innerw = $w - $bl / 2 - $br / 2 - $cell['padding']['L'] - $cell['padding']['R'];
								}
							} elseif ($this->simpleTables) {
								if ($table['borders_separate']) {
									$innerw = $w - $table['simple']['border_details']['L']['w'] - $table['simple']['border_details']['R']['w'] - $cell['padding']['L'] - $cell['padding']['R'] - $table['border_spacing_H'];
								} else {
									$innerw = $w - $table['simple']['border_details']['L']['w'] / 2 - $table['simple']['border_details']['R']['w'] / 2 - $cell['padding']['L'] - $cell['padding']['R'];
								}
							}
							if ($cell['a'] == 'C' || $this->table[($level + 1)][$objattr['nestedcontent']]['a'] == 'C') {
								$ncx += ($innerw - $ntw) / 2;
							} elseif ($cell['a'] == 'R' || $this->table[($level + 1)][$objattr['nestedcontent']]['a'] == 'R') {
								$ncx += $innerw - $ntw;
							}
							$this->x = $ncx;

							$this->_tableWrite($this->table[($level + 1)][$objattr['nestedcontent']]);
							$this->cellBorderBuffer = $save_buffer;
							$this->x = $bak_x;
							$this->divwidth = $save_dw;
						}

						$this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, false, $blockdir, $table_draft);
					}
				} else {
					/* -- END TABLES -- */
					if ($is_table) { // *TABLES*
						$maxWidth = $this->divwidth;  // *TABLES*
					} // *TABLES*
					else { // *TABLES*
						$maxWidth = $this->divwidth - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w']);
					} // *TABLES*

					/* -- CSS-IMAGE-FLOAT -- */
					// If float (already) exists at this level
					if (isset($this->floatmargins['R']) && $this->y <= $this->floatmargins['R']['y1'] && $this->y >= $this->floatmargins['R']['y0']) {
						$maxWidth -= $this->floatmargins['R']['w'];
					}
					if (isset($this->floatmargins['L']) && $this->y <= $this->floatmargins['L']['y1'] && $this->y >= $this->floatmargins['L']['y0']) {
						$maxWidth -= $this->floatmargins['L']['w'];
					}
					/* -- END CSS-IMAGE-FLOAT -- */

					list($skipln) = $this->inlineObject($objattr['type'], '', $this->y, $objattr, $this->lMargin, ($this->flowingBlockAttr['contentWidth'] / fpdf_protection::SCALE), $maxWidth, $this->flowingBlockAttr['height'], false, $is_table);
					//  1 -> New line needed because of width
					// -1 -> Will fit width on line but NEW PAGE REQUIRED because of height
					// -2 -> Will not fit on line therefore needs new line but thus NEW PAGE REQUIRED
					$iby = $this->y;
					$oldpage = $this->page;
					$oldcol = $this->CurrCol;
					if (($skipln == 1 || $skipln == -2) && !isset($objattr['float'])) {
						$this->finishFlowingBlock(false, $objattr['type']);
						$this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, false, $blockdir, $table_draft);
					}

					if (!$table_draft) {
						$thispage = $this->page;
						if ($this->CurrCol != $oldcol) {
							$changedcol = true;
						} else {
							$changedcol = false;
						}

						// the previous lines can already have triggered page break or column change
						if (!$changedcol && $skipln < 0 && $this->AcceptPageBreak() && $thispage == $oldpage) {
							$this->AddPage($this->CurOrientation);

							// Added to correct Images already set on line before page advanced
							// i.e. if second inline image on line is higher than first and forces new page
							if (count($this->objectbuffer)) {
								$yadj = $iby - $this->y;
								foreach ($this->objectbuffer as $ib => $val) {
									if ($this->objectbuffer[$ib]['OUTER-Y']) {
										$this->objectbuffer[$ib]['OUTER-Y'] -= $yadj;
									}
									if ($this->objectbuffer[$ib]['BORDER-Y']) {
										$this->objectbuffer[$ib]['BORDER-Y'] -= $yadj;
									}
									if ($this->objectbuffer[$ib]['INNER-Y']) {
										$this->objectbuffer[$ib]['INNER-Y'] -= $yadj;
									}
								}
							}
						}

						// Added to correct for OddEven Margins
						if ($this->page != $oldpage) {
							if (($this->page - $oldpage) % 2 == 1) {
								$bak_x += $this->MarginCorrection;
							}
							$oldpage = $this->page;
							$y = $this->tMargin - $paint_ht_corr;
							$this->oldy = $this->tMargin - $paint_ht_corr;
							$old_height = 0;
						}
						$this->x = $bak_x;
						/* -- COLUMNS -- */
						// COLS
						// OR COLUMN CHANGE
						if ($this->CurrCol != $oldcolumn) {
							if ($this->directionality == 'rtl') { // *OTL*
								$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
							} // *OTL*
							else { // *OTL*
								$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
							} // *OTL*
							$this->x = $bak_x;
							$oldcolumn = $this->CurrCol;
							$y = $this->y0 - $paint_ht_corr;
							$this->oldy = $this->y0 - $paint_ht_corr;
							$old_height = 0;
						}
						/* -- END COLUMNS -- */
					}

					/* -- CSS-IMAGE-FLOAT -- */
					if ($objattr['type'] == 'image' && isset($objattr['float'])) {
						$fy = $this->y;

						// DIV TOP MARGIN/BORDER/PADDING
						if ($this->flowingBlockAttr['newblock'] && ($this->flowingBlockAttr['blockstate'] == 1 || $this->flowingBlockAttr['blockstate'] == 3) && $this->flowingBlockAttr['lineCount'] == 0) {
							$fy += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
						}

						if ($objattr['float'] == 'R') {
							$fx = $this->w - $this->rMargin - $objattr['width'] - ($this->blk[$this->blklvl]['outer_right_margin'] + $this->blk[$this->blklvl]['border_right']['w'] + $this->blk[$this->blklvl]['padding_right']);
						} elseif ($objattr['float'] == 'L') {
							$fx = $this->lMargin + ($this->blk[$this->blklvl]['outer_left_margin'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_left']);
						}
						$w = $objattr['width'];
						$h = abs($objattr['height']);

						$widthLeft = $maxWidth - ($this->flowingBlockAttr['contentWidth'] / fpdf_protection::SCALE);
						$maxHeight = $this->h - ($this->tMargin + $this->margin_header + $this->bMargin + 10);
						// For Images
						$extraWidth = ($objattr['border_left']['w'] + $objattr['border_right']['w'] + $objattr['margin_left'] + $objattr['margin_right']);
						$extraHeight = ($objattr['border_top']['w'] + $objattr['border_bottom']['w'] + $objattr['margin_top'] + $objattr['margin_bottom']);

						if ($objattr['itype'] == 'wmf' || $objattr['itype'] == 'svg') {
							$file = $objattr['file'];
							$info = $this->formobjects[$file];
						} else {
							$file = $objattr['file'];
							$info = $this->images[$file];
						}
						$img_w = $w - $extraWidth;
						$img_h = $h - $extraHeight;
						if ($objattr['border_left']['w']) {
							$objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w'] + $objattr['border_right']['w']) / 2);
							$objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w'] + $objattr['border_bottom']['w']) / 2);
							$objattr['BORDER-X'] = $fx + $objattr['margin_left'] + (($objattr['border_left']['w']) / 2);
							$objattr['BORDER-Y'] = $fy + $objattr['margin_top'] + (($objattr['border_top']['w']) / 2);
						}
						$objattr['INNER-WIDTH'] = $img_w;
						$objattr['INNER-HEIGHT'] = $img_h;
						$objattr['INNER-X'] = $fx + $objattr['margin_left'] + ($objattr['border_left']['w']);
						$objattr['INNER-Y'] = $fy + $objattr['margin_top'] + ($objattr['border_top']['w']);
						$objattr['ID'] = $info['i'];
						$objattr['OUTER-WIDTH'] = $w;
						$objattr['OUTER-HEIGHT'] = $h;
						$objattr['OUTER-X'] = $fx;
						$objattr['OUTER-Y'] = $fy;
						if ($objattr['float'] == 'R') {
							// If R float already exists at this level
							$this->floatmargins['R']['skipline'] = false;
							if (isset($this->floatmargins['R']['y1']) && $this->floatmargins['R']['y1'] > 0 && $fy < $this->floatmargins['R']['y1']) {
								$this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
							} // If L float already exists at this level
							elseif (isset($this->floatmargins['L']['y1']) && $this->floatmargins['L']['y1'] > 0 && $fy < $this->floatmargins['L']['y1']) {
								// Final check distance between floats is not now too narrow to fit text
								$mw = 2 * $this->GetCharWidth('W', false);
								if (($this->blk[$this->blklvl]['inner_width'] - $w - $this->floatmargins['L']['w']) < $mw) {
									$this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
								} else {
									$this->floatmargins['R']['x'] = $fx;
									$this->floatmargins['R']['w'] = $w;
									$this->floatmargins['R']['y0'] = $fy;
									$this->floatmargins['R']['y1'] = $fy + $h;
									if ($skipln == 1) {
										$this->floatmargins['R']['skipline'] = true;
										$this->floatmargins['R']['id'] = count($this->floatbuffer) + 0;
										$objattr['skipline'] = true;
									}
									$this->floatbuffer[] = $objattr;
								}
							} else {
								$this->floatmargins['R']['x'] = $fx;
								$this->floatmargins['R']['w'] = $w;
								$this->floatmargins['R']['y0'] = $fy;
								$this->floatmargins['R']['y1'] = $fy + $h;
								if ($skipln == 1) {
									$this->floatmargins['R']['skipline'] = true;
									$this->floatmargins['R']['id'] = count($this->floatbuffer) + 0;
									$objattr['skipline'] = true;
								}
								$this->floatbuffer[] = $objattr;
							}
						} elseif ($objattr['float'] == 'L') {
							// If L float already exists at this level
							$this->floatmargins['L']['skipline'] = false;
							if (isset($this->floatmargins['L']['y1']) && $this->floatmargins['L']['y1'] > 0 && $fy < $this->floatmargins['L']['y1']) {
								$this->floatmargins['L']['skipline'] = false;
								$this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
							} // If R float already exists at this level
							elseif (isset($this->floatmargins['R']['y1']) && $this->floatmargins['R']['y1'] > 0 && $fy < $this->floatmargins['R']['y1']) {
								// Final check distance between floats is not now too narrow to fit text
								$mw = 2 * $this->GetCharWidth('W', false);
								if (($this->blk[$this->blklvl]['inner_width'] - $w - $this->floatmargins['R']['w']) < $mw) {
									$this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
								} else {
									$this->floatmargins['L']['x'] = $fx + $w;
									$this->floatmargins['L']['w'] = $w;
									$this->floatmargins['L']['y0'] = $fy;
									$this->floatmargins['L']['y1'] = $fy + $h;
									if ($skipln == 1) {
										$this->floatmargins['L']['skipline'] = true;
										$this->floatmargins['L']['id'] = count($this->floatbuffer) + 0;
										$objattr['skipline'] = true;
									}
									$this->floatbuffer[] = $objattr;
								}
							} else {
								$this->floatmargins['L']['x'] = $fx + $w;
								$this->floatmargins['L']['w'] = $w;
								$this->floatmargins['L']['y0'] = $fy;
								$this->floatmargins['L']['y1'] = $fy + $h;
								if ($skipln == 1) {
									$this->floatmargins['L']['skipline'] = true;
									$this->floatmargins['L']['id'] = count($this->floatbuffer) + 0;
									$objattr['skipline'] = true;
								}
								$this->floatbuffer[] = $objattr;
							}
						}
					} else {
						/* -- END CSS-IMAGE-FLOAT -- */
						$this->WriteFlowingBlock($vetor[0], (isset($vetor[18]) ? $vetor[18] : null));  // mPDF 5.7.1
						/* -- CSS-IMAGE-FLOAT -- */
					}
					/* -- END CSS-IMAGE-FLOAT -- */
				} // *TABLES*
			} // END If special content
			else { // THE text
				if ($this->tableLevel) {
					$paint_ht_corr = 0;
				} // To move the y up when new column/page started if div border needed
				else {
					$paint_ht_corr = $this->blk[$this->blklvl]['border_top']['w'];
				}

				if ($vetor[0] == "\n") { // We are reading a <BR> now turned into newline ("\n")
					if ($this->flowingBlockAttr['content']) {
						$this->finishFlowingBlock(false, 'br');
					} elseif ($is_table) {
						$this->y+= $this->_computeLineheight($this->cellLineHeight);
					} elseif (!$is_table) {
						$this->DivLn($this->lineheight);
						if ($this->ColActive) {
							$this->breakpoints[$this->CurrCol][] = $this->y;
						} // *COLUMNS*
					}
					// Added to correct for OddEven Margins
					if ($this->page != $oldpage) {
						if (($this->page - $oldpage) % 2 == 1) {
							$bak_x += $this->MarginCorrection;
						}
						$oldpage = $this->page;
						$y = $this->tMargin - $paint_ht_corr;
						$this->oldy = $this->tMargin - $paint_ht_corr;
						$old_height = 0;
					}
					$this->x = $bak_x;
					/* -- COLUMNS -- */
					// COLS
					// OR COLUMN CHANGE
					if ($this->CurrCol != $oldcolumn) {
						if ($this->directionality == 'rtl') { // *OTL*
							$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
						} // *OTL*
						else { // *OTL*
							$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
						} // *OTL*
						$this->x = $bak_x;
						$oldcolumn = $this->CurrCol;
						$y = $this->y0 - $paint_ht_corr;
						$this->oldy = $this->y0 - $paint_ht_corr;
						$old_height = 0;
					}
					/* -- END COLUMNS -- */
					$this->newFlowingBlock($this->divwidth, $this->divheight, $align, $is_table, $blockstate, false, $blockdir, $table_draft);
				} else {
					$this->WriteFlowingBlock($vetor[0], $vetor[18]);  // mPDF 5.7.1
					// Added to correct for OddEven Margins
					if ($this->page != $oldpage) {
						if (($this->page - $oldpage) % 2 == 1) {
							$bak_x += $this->MarginCorrection;
							$this->x = $bak_x;
						}
						$oldpage = $this->page;
						$y = $this->tMargin - $paint_ht_corr;
						$this->oldy = $this->tMargin - $paint_ht_corr;
						$old_height = 0;
					}
					/* -- COLUMNS -- */
					// COLS
					// OR COLUMN CHANGE
					if ($this->CurrCol != $oldcolumn) {
						if ($this->directionality == 'rtl') { // *OTL*
							$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
						} // *OTL*
						else { // *OTL*
							$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
						} // *OTL*
						$this->x = $bak_x;
						$oldcolumn = $this->CurrCol;
						$y = $this->y0 - $paint_ht_corr;
						$this->oldy = $this->y0 - $paint_ht_corr;
						$old_height = 0;
					}
					/* -- END COLUMNS -- */
				}
			}

			// Check if it is the last element. If so then finish printing the block
			if ($i == ($array_size - 1)) {
				$this->finishFlowingBlock(true); // true = END of flowing block
				// Added to correct for OddEven Margins
				if ($this->page != $oldpage) {
					if (($this->page - $oldpage) % 2 == 1) {
						$bak_x += $this->MarginCorrection;
						$this->x = $bak_x;
					}
					$oldpage = $this->page;
					$y = $this->tMargin - $paint_ht_corr;
					$this->oldy = $this->tMargin - $paint_ht_corr;
					$old_height = 0;
				}

				/* -- COLUMNS -- */
				// COLS
				// OR COLUMN CHANGE
				if ($this->CurrCol != $oldcolumn) {
					if ($this->directionality == 'rtl') { // *OTL*
						$bak_x -= ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
					} // *OTL*
					else { // *OTL*
						$bak_x += ($this->CurrCol - $oldcolumn) * ($this->ColWidth + $this->ColGap);
					} // *OTL*
					$this->x = $bak_x;
					$oldcolumn = $this->CurrCol;
					$y = $this->y0 - $paint_ht_corr;
					$this->oldy = $this->y0 - $paint_ht_corr;
					$old_height = 0;
				}
				/* -- END COLUMNS -- */
			}

			// RESETTING VALUES
			$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
			$this->colorarray = '';
			$this->spanbgcolorarray = '';
			$this->spanbgcolor = false;
			$this->spanborder = false;
			$this->spanborddet = [];
			$this->HREF = '';
			$this->textparam = [];
			$this->SetTextOutline();

			$this->textvar = 0x00; // mPDF 5.7.1
			$this->OTLtags = [];
			$this->textshadow = '';

			$this->currentfontfamily = '';
			$this->currentfontsize = '';
			$this->currentfontstyle = '';
			$this->currentLang = $this->default_lang;  // mPDF 6
			$this->RestrictUnicodeFonts($this->default_available_fonts); // mPDF 6
			/* -- TABLES -- */
			if ($this->tableLevel) {
				$this->SetLineHeight('', $this->table[1][1]['cellLineHeight']); // *TABLES*
			} else { 			/* -- END TABLES -- */
				if (isset($this->blk[$this->blklvl]['line_height']) && $this->blk[$this->blklvl]['line_height']) {
					$this->SetLineHeight('', $this->blk[$this->blklvl]['line_height']); // sets default line height
				}
			}
			$this->ResetStyles();
			$this->lSpacingCSS = '';
			$this->wSpacingCSS = '';
			$this->fixedlSpacing = false;
			$this->minwSpacing = 0;
			$this->SetDash();
			$this->dash_on = false;
			$this->dotted_on = false;
		}//end of for(i=0;i<arraysize;i++)

		$this->Reset(); // mPDF 6
		// PAINT DIV BORDER	// DISABLED IN COLUMNS AS DOESN'T WORK WHEN BROKEN ACROSS COLS??
		if ((isset($this->blk[$this->blklvl]['border']) || isset($this->blk[$this->blklvl]['bgcolor']) || isset($this->blk[$this->blklvl]['box_shadow'])) && $blockstate && ($this->y != $this->oldy)) {
			$bottom_y = $this->y; // Does not include Bottom Margin
			if (isset($this->blk[$this->blklvl]['startpage']) && $this->blk[$this->blklvl]['startpage'] != $this->page && $blockstate != 1) {
				$this->PaintDivBB('pagetop', $blockstate);
			} elseif ($blockstate != 1) {
				$this->PaintDivBB('', $blockstate);
			}
			$this->y = $bottom_y;
			$this->x = $bak_x;
		}

		// Reset Font
		$this->SetFontSize($this->default_font_size, false);
		if ($table_draft) {
			$ch = $this->y - $bak_y;
			$this->y = $bak_y;
			$this->x = $bak_x;
			return $ch;
		}
	}

	function WriteHTML($html, $sub = 0, $init = true, $close = true)
	{
		/* Check $html is an integer, float, string, boolean or class with __toString(), otherwise throw exception */
		if (is_scalar($html) === false) {
			if (!is_object($html) || ! method_exists($html, '__toString')) {
				throw new MpdfException('WriteHTML() requires $html be an integer, float, string, boolean or an object with the __toString() magic method.');
			}
		}

		/* Cast $html as a string */
		$html = (string) $html;

		// @log Parsing CSS & Headers

		if ($init) {
			$this->headerbuffer = '';
			$this->textbuffer = [];
			$this->fixedPosBlockSave = [];
		}
		if ($sub == 1) {
			$html = '<style> ' . $html . ' </style>';
		} // stylesheet only

		if ($this->allow_charset_conversion) {
			if ($sub < 1) {
				$this->ReadCharset($html);
			}
			if ($this->charset_in && $sub != 4) {
				$success = iconv($this->charset_in, 'UTF-8//TRANSLIT', $html);
				if ($success) {
					$html = $success;
				}
			}
		}

		$html = $this->purify_utf8($html, false);
		if ($init) {
			$this->blklvl = 0;
			$this->lastblocklevelchange = 0;
			$this->blk = [];
			$this->initialiseBlock($this->blk[0]);
			$this->blk[0]['width'] = & $this->pgwidth;
			$this->blk[0]['inner_width'] = & $this->pgwidth;
			$this->blk[0]['blockContext'] = $this->blockContext;
		}

		$zproperties = [];
		if ($sub < 2) {
			$this->ReadMetaTags($html);

			if (preg_match('/<base[^>]*href=["\']([^"\'>]*)["\']/i', $html, $m)) {
				$this->SetBasePath($m[1]);
			}
			$html = $this->cssManager->ReadCSS($html);

			if ($this->autoLangToFont && !$this->usingCoreFont && preg_match('/<html [^>]*lang=[\'\"](.*?)[\'\"]/ism', $html, $m)) {
				$html_lang = $m[1];
			}

			if (preg_match('/<html [^>]*dir=[\'\"]\s*rtl\s*[\'\"]/ism', $html)) {
				$zproperties['DIRECTION'] = 'rtl';
			}

			// allow in-line CSS for body tag to be parsed // Get <body> tag inline CSS
			if (preg_match('/<body([^>]*)>(.*?)<\/body>/ism', $html, $m) || preg_match('/<body([^>]*)>(.*)$/ism', $html, $m)) {
				$html = $m[2];
				// Changed to allow style="background: url('bg.jpg')"
				if (preg_match('/style=[\"](.*?)[\"]/ism', $m[1], $mm) || preg_match('/style=[\'](.*?)[\']/ism', $m[1], $mm)) {
					$zproperties = $this->cssManager->readInlineCSS($mm[1]);
				}
				if (preg_match('/dir=[\'\"]\s*rtl\s*[\'\"]/ism', $m[1])) {
					$zproperties['DIRECTION'] = 'rtl';
				}
				if (isset($html_lang) && $html_lang) {
					$zproperties['LANG'] = $html_lang;
				}
				if ($this->autoLangToFont && !$this->onlyCoreFonts && preg_match('/lang=[\'\"](.*?)[\'\"]/ism', $m[1], $mm)) {
					$zproperties['LANG'] = $mm[1];
				}
			}
		}
		$properties = $this->cssManager->MergeCSS('BLOCK', 'BODY', '');
		if ($zproperties) {
			$properties = $this->cssManager->array_merge_recursive_unique($properties, $zproperties);
		}

		if (isset($properties['DIRECTION']) && $properties['DIRECTION']) {
			$this->cssManager->CSS['BODY']['DIRECTION'] = $properties['DIRECTION'];
		}
		if (!isset($this->cssManager->CSS['BODY']['DIRECTION'])) {
			$this->cssManager->CSS['BODY']['DIRECTION'] = $this->directionality;
		} else {
			$this->SetDirectionality($this->cssManager->CSS['BODY']['DIRECTION']);
		}

		$this->setCSS($properties, '', 'BODY');

		$this->blk[0]['InlineProperties'] = $this->saveInlineProperties();

		if ($sub == 1) {
			return '';
		}
		if (!isset($this->cssManager->CSS['BODY'])) {
			$this->cssManager->CSS['BODY'] = [];
		}

		/* -- BACKGROUNDS -- */
		if (isset($properties['BACKGROUND-GRADIENT'])) {
			$this->bodyBackgroundGradient = $properties['BACKGROUND-GRADIENT'];
		}

		if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE']) {
			$ret = $this->SetBackground($properties, $this->pgwidth);
			if ($ret) {
				$this->bodyBackgroundImage = $ret;
			}
		}
		/* -- END BACKGROUNDS -- */

		/* -- CSS-PAGE -- */
		// If page-box is set
		if ($this->state == 0 && ((isset($this->cssManager->CSS['@PAGE']) && $this->cssManager->CSS['@PAGE']) || (isset($this->cssManager->CSS['@PAGE>>PSEUDO>>FIRST']) && $this->cssManager->CSS['@PAGE>>PSEUDO>>FIRST']))) { // mPDF 5.7.3
			$this->page_box['current'] = '';
			$this->page_box['using'] = true;
			list($pborientation, $pbmgl, $pbmgr, $pbmgt, $pbmgb, $pbmgh, $pbmgf, $hname, $fname, $bg, $resetpagenum, $pagenumstyle, $suppress, $marks, $newformat) = $this->SetPagedMediaCSS('', false, 'O');
			$this->DefOrientation = $this->CurOrientation = $pborientation;
			$this->orig_lMargin = $this->DeflMargin = $pbmgl;
			$this->orig_rMargin = $this->DefrMargin = $pbmgr;
			$this->orig_tMargin = $this->tMargin = $pbmgt;
			$this->orig_bMargin = $this->bMargin = $pbmgb;
			$this->orig_hMargin = $this->margin_header = $pbmgh;
			$this->orig_fMargin = $this->margin_footer = $pbmgf;
			list($pborientation, $pbmgl, $pbmgr, $pbmgt, $pbmgb, $pbmgh, $pbmgf, $hname, $fname, $bg, $resetpagenum, $pagenumstyle, $suppress, $marks, $newformat) = $this->SetPagedMediaCSS('', true, 'O'); // first page
			$this->show_marks = $marks;
			if ($hname) {
				$this->firstPageBoxHeader = $hname;
			}
			if ($fname) {
				$this->firstPageBoxFooter = $fname;
			}
		}
		/* -- END CSS-PAGE -- */

		$parseonly = false;
		$this->bufferoutput = false;
		if ($sub == 3) {
			$parseonly = true;
			// Close any open block tags
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
			}
			// Output any text left in buffer
			if (count($this->textbuffer)) {
				$this->printbuffer($this->textbuffer);
			}
			$this->textbuffer = [];
		} elseif ($sub == 4) {
			// Close any open block tags
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
			}
			// Output any text left in buffer
			if (count($this->textbuffer)) {
				$this->printbuffer($this->textbuffer);
			}
			$this->bufferoutput = true;
			$this->textbuffer = [];
			$this->headerbuffer = '';
			$properties = $this->cssManager->MergeCSS('BLOCK', 'BODY', '');
			$this->setCSS($properties, '', 'BODY');
		}

		mb_internal_encoding('UTF-8');

		$html = $this->AdjustHTML($html, $this->tabSpaces); // Try to make HTML look more like XHTML

		if ($this->autoScriptToLang) {
			$html = $this->markScriptToLang($html);
		}

		preg_match_all('/<htmlpageheader([^>]*)>(.*?)<\/htmlpageheader>/si', $html, $h);
		for ($i = 0; $i < count($h[1]); $i++) {
			if (preg_match('/name=[\'|\"](.*?)[\'|\"]/', $h[1][$i], $n)) {
				$this->pageHTMLheaders[$n[1]]['html'] = $h[2][$i];
				$this->pageHTMLheaders[$n[1]]['h'] = $this->_getHtmlHeight($h[2][$i]);
			}
		}
		preg_match_all('/<htmlpagefooter([^>]*)>(.*?)<\/htmlpagefooter>/si', $html, $f);
		for ($i = 0; $i < count($f[1]); $i++) {
			if (preg_match('/name=[\'|\"](.*?)[\'|\"]/', $f[1][$i], $n)) {
				$this->pageHTMLfooters[$n[1]]['html'] = $f[2][$i];
				$this->pageHTMLfooters[$n[1]]['h'] = $this->_getHtmlHeight($f[2][$i]);
			}
		}

		$html = preg_replace('/<htmlpageheader.*?<\/htmlpageheader>/si', '', $html);
		$html = preg_replace('/<htmlpagefooter.*?<\/htmlpagefooter>/si', '', $html);

		if ($this->state == 0 && $sub != 1 && $sub != 3 && $sub != 4) {
			$this->AddPage($this->CurOrientation);
		}


		if (isset($hname) && preg_match('/^html_(.*)$/i', $hname, $n)) {
			$this->SetHTMLHeader($this->pageHTMLheaders[$n[1]], 'O', true);
		}
		if (isset($fname) && preg_match('/^html_(.*)$/i', $fname, $n)) {
			$this->SetHTMLFooter($this->pageHTMLfooters[$n[1]], 'O');
		}



		$html = str_replace('<?', '< ', $html); // Fix '<?XML' bug from HTML code generated by MS Word

		$this->checkSIP = false;
		$this->checkSMP = false;
		$this->checkCJK = false;
		if ($this->onlyCoreFonts) {
			$html = $this->SubstituteChars($html);
		} else {
			if (preg_match("/([" . $this->pregRTLchars . "])/u", $html)) {
				$this->biDirectional = true;
			} // *OTL*
			if (preg_match("/([\x{20000}-\x{2FFFF}])/u", $html)) {
				$this->checkSIP = true;
			}
			if (preg_match("/([\x{10000}-\x{1FFFF}])/u", $html)) {
				$this->checkSMP = true;
			}
			/* -- CJK-FONTS -- */
			if (preg_match("/([" . $this->pregCJKchars . "])/u", $html)) {
				$this->checkCJK = true;
			}
			/* -- END CJK-FONTS -- */
		}

		// Don't allow non-breaking spaces that are converted to substituted chars or will break anyway and mess up table width calc.
		$html = str_replace('<tta>160</tta>', chr(32), $html);
		$html = str_replace('</tta><tta>', '|', $html);
		$html = str_replace('</tts><tts>', '|', $html);
		$html = str_replace('</ttz><ttz>', '|', $html);

		// Add new supported tags in the DisableTags function
		$html = strip_tags($html, $this->enabledtags); // remove all unsupported tags, but the ones inside the 'enabledtags' string
		// Explode the string in order to parse the HTML code
		$a = preg_split('/<(.*?)>/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		// ? more accurate regexp that allows e.g. <a name="Silly <name>">
		// if changing - also change in fn.SubstituteChars()
		// $a = preg_split ('/<((?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+)>/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

		if ($this->mb_enc) {
			mb_internal_encoding($this->mb_enc);
		}
		$pbc = 0;
		$this->subPos = -1;
		$cnt = count($a);
		for ($i = 0; $i < $cnt; $i++) {
			$e = $a[$i];
			if ($i % 2 == 0) {
				// TEXT
				if ($this->blk[$this->blklvl]['hide']) {
					continue;
				}
				if ($this->inlineDisplayOff) {
					continue;
				}
				if ($this->inMeter) {
					continue;
				}

				if ($this->inFixedPosBlock) {
					$this->fixedPosBlock .= $e;
					continue;
				} // *CSS-POSITION*
				if (strlen($e) == 0) {
					continue;
				}

				if ($this->ignorefollowingspaces && !$this->ispre) {
					if (strlen(ltrim($e)) == 0) {
						continue;
					}
					if ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats' && substr($e, 0, 1) == ' ') {
						$this->ignorefollowingspaces = false;
						$e = ltrim($e);
					}
				}

				$this->OTLdata = null;  // mPDF 5.7.1

				$e = UtfString::strcode2utf($e);
				$e = $this->lesser_entity_decode($e);

				if ($this->usingCoreFont) {
					// If core font is selected in document which is not onlyCoreFonts - substitute with non-core font
					if ($this->useSubstitutions && !$this->onlyCoreFonts && $this->subPos < $i && !$this->specialcontent) {
						$cnt += $this->SubstituteCharsNonCore($a, $i, $e);
					}
					// CONVERT ENCODING
					$e = mb_convert_encoding($e, $this->mb_enc, 'UTF-8');
					if ($this->textvar & TextVars::FT_UPPERCASE) {
						$e = mb_strtoupper($e, $this->mb_enc);
					} // mPDF 5.7.1
					elseif ($this->textvar & TextVars::FT_LOWERCASE) {
						$e = mb_strtolower($e, $this->mb_enc);
					} // mPDF 5.7.1
					elseif ($this->textvar & TextVars::FT_CAPITALIZE) {
						$e = mb_convert_case($e, MB_CASE_TITLE, "UTF-8");
					} // mPDF 5.7.1
				} else {
					if ($this->checkSIP && $this->CurrentFont['sipext'] && $this->subPos < $i && (!$this->specialcontent || !$this->useActiveForms)) {
						$cnt += $this->SubstituteCharsSIP($a, $i, $e);
					}

					if ($this->useSubstitutions && !$this->onlyCoreFonts && $this->CurrentFont['type'] != 'Type0' && $this->subPos < $i && (!$this->specialcontent || !$this->useActiveForms)) {
						$cnt += $this->SubstituteCharsMB($a, $i, $e);
					}

					if ($this->textvar & TextVars::FT_UPPERCASE) {
						$e = mb_strtoupper($e, $this->mb_enc);
					} elseif ($this->textvar & TextVars::FT_LOWERCASE) {
						$e = mb_strtolower($e, $this->mb_enc);
					} elseif ($this->textvar & TextVars::FT_CAPITALIZE) {
						$e = mb_convert_case($e, MB_CASE_TITLE, "UTF-8");
					}

					/* -- OTL -- */
					// Use OTL OpenType Table Layout - GSUB & GPOS
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL'] && (!$this->specialcontent || !$this->useActiveForms)) {
						if (!$this->otl) {
							$this->otl = new Otl($this, $this->fontCache);
						}
						$e = $this->otl->applyOTL($e, $this->CurrentFont['useOTL']);
						$this->OTLdata = $this->otl->OTLdata;
						$this->otl->removeChar($e, $this->OTLdata, "\xef\xbb\xbf"); // Remove ZWNBSP (also Byte order mark FEFF)
					} /* -- END OTL -- */
					else {
						// removes U+200E/U+200F LTR and RTL mark and U+200C/U+200D Zero-width Joiner and Non-joiner
						$e = preg_replace("/[\xe2\x80\x8c\xe2\x80\x8d\xe2\x80\x8e\xe2\x80\x8f]/u", '', $e);
						$e = preg_replace("/[\xef\xbb\xbf]/u", '', $e); // Remove ZWNBSP (also Byte order mark FEFF)
					}
				}

				if (($this->tts) || ($this->ttz) || ($this->tta)) {
					$es = explode('|', $e);
					$e = '';
					foreach ($es as $val) {
						$e .= chr($val);
					}
				}

				//  FORM ELEMENTS
				if ($this->specialcontent) {
					/* -- FORMS -- */
					// SELECT tag (form element)
					if ($this->specialcontent == "type=select") {
						$e = ltrim($e);
						if (!empty($this->OTLdata)) {
							$this->otl->trimOTLdata($this->OTLdata, true, false);
						} // *OTL*
						$stringwidth = $this->GetStringWidth($e);
						if (!isset($this->selectoption['MAXWIDTH']) || $stringwidth > $this->selectoption['MAXWIDTH']) {
							$this->selectoption['MAXWIDTH'] = $stringwidth;
						}
						if (!isset($this->selectoption['SELECTED']) || $this->selectoption['SELECTED'] == '') {
							$this->selectoption['SELECTED'] = $e;
							if (!empty($this->OTLdata)) {
								$this->selectoption['SELECTED-OTLDATA'] = $this->OTLdata;
							} // *OTL*
						}
						// Active Forms
						if (isset($this->selectoption['ACTIVE']) && $this->selectoption['ACTIVE']) {
							$this->selectoption['ITEMS'][] = ['exportValue' => $this->selectoption['currentVAL'], 'content' => $e, 'selected' => $this->selectoption['currentSEL']];
						}
						$this->OTLdata = [];
					} // TEXTAREA
					else {
						$objattr = unserialize($this->specialcontent);
						$objattr['text'] = $e;
						$objattr['OTLdata'] = $this->OTLdata;
						$this->OTLdata = [];
						$te = "\xbb\xa4\xactype=textarea,objattr=" . serialize($objattr) . "\xbb\xa4\xac";
						if ($this->tdbegin) {
							$this->_saveCellTextBuffer($te, $this->HREF);
						} else {
							$this->_saveTextBuffer($te, $this->HREF);
						}
					}
					/* -- END FORMS -- */
				} // TABLE
				elseif ($this->tableLevel) {
					/* -- TABLES -- */
					if ($this->tdbegin) {
						if (($this->ignorefollowingspaces) && !$this->ispre) {
							$e = ltrim($e);
							if (!empty($this->OTLdata)) {
								$this->otl->trimOTLdata($this->OTLdata, true, false);
							} // *OTL*
						}
						if ($e || $e === '0') {
							if ($this->blockjustfinished && $this->cell[$this->row][$this->col]['s'] > 0) {
								$this->_saveCellTextBuffer("\n");
								if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
									$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
								} elseif ($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
									$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
								}
								$this->cell[$this->row][$this->col]['s'] = 0; // reset
							}
							$this->blockjustfinished = false;

							if (!isset($this->cell[$this->row][$this->col]['R']) || !$this->cell[$this->row][$this->col]['R']) {
								if (isset($this->cell[$this->row][$this->col]['s'])) {
									$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($e, false, $this->OTLdata, $this->textvar);
								} else {
									$this->cell[$this->row][$this->col]['s'] = $this->GetStringWidth($e, false, $this->OTLdata, $this->textvar);
								}
								if (!empty($this->spanborddet)) {
									$this->cell[$this->row][$this->col]['s'] += (isset($this->spanborddet['L']['w']) ? $this->spanborddet['L']['w'] : 0) + (isset($this->spanborddet['R']['w']) ? $this->spanborddet['R']['w'] : 0);
								}
							}
							$this->_saveCellTextBuffer($e, $this->HREF);
							if (substr($this->cell[$this->row][$this->col]['a'], 0, 1) == 'D') {
								$dp = $this->decimal_align[substr($this->cell[$this->row][$this->col]['a'], 0, 2)];
								$s = preg_split('/' . preg_quote($dp, '/') . '/', $e, 2);  // ? needs to be /u if not core
								$s0 = $this->GetStringWidth($s[0], false);
								if (isset($s[1]) && $s[1]) {
									$s1 = $this->GetStringWidth(($s[1] . $dp), false);
								} else {
									$s1 = 0;
								}
								if (!isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'])) {
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'] = $s0;
								} else {
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'] = max($s0, $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0']);
								}
								if (!isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'])) {
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'] = $s1;
								} else {
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'] = max($s1, $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1']);
								}
							}

							$this->nestedtablejustfinished = false;
							$this->linebreakjustfinished = false;
						}
					}
					/* -- END TABLES -- */
				} // ALL ELSE
				else {
					if ($this->ignorefollowingspaces && !$this->ispre) {
						$e = ltrim($e);
						if (!empty($this->OTLdata)) {
							$this->otl->trimOTLdata($this->OTLdata, true, false);
						} // *OTL*
					}
					if ($e || $e === '0') {
						$this->_saveTextBuffer($e, $this->HREF);
					}
				}
				if ($e || $e === '0') {
					$this->ignorefollowingspaces = false; // mPDF 6
				}
				if (substr($e, -1, 1) == ' ' && !$this->ispre && $this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
					$this->ignorefollowingspaces = true;
				}
			} else { // TAG **
				if (isset($e[0]) && $e[0] == '/') {
					$endtag = trim(strtoupper(substr($e, 1)));

					/* -- CSS-POSITION -- */
					// mPDF 6
					if ($this->inFixedPosBlock) {
						if (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags)) {
							$this->fixedPosBlockDepth--;
						}
						if ($this->fixedPosBlockDepth == 0) {
							$this->fixedPosBlockSave[] = [$this->fixedPosBlock, $this->fixedPosBlockBBox, $this->page];
							$this->fixedPosBlock = '';
							$this->inFixedPosBlock = false;
							continue;
						}
						$this->fixedPosBlock .= '<' . $e . '>';
						continue;
					}
					/* -- END CSS-POSITION -- */

					// mPDF 6
					// Correct for tags where HTML5 specifies optional end tags (see also OpenTag() )
					if ($this->allow_html_optional_endtags && !$parseonly) {
						if (isset($this->blk[$this->blklvl]['tag'])) {
							$closed = false;
							// li end tag may be omitted if there is no more content in the parent element
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'LI' && $endtag != 'LI' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('LI', $a, $i);
								$closed = true;
							}
							// dd end tag may be omitted if there is no more content in the parent element
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'DD' && $endtag != 'DD' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('DD', $a, $i);
								$closed = true;
							}
							// p end tag may be omitted if there is no more content in the parent element and the parent element is not an A element [??????]
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'P' && $endtag != 'P' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('P', $a, $i);
								$closed = true;
							}
							// option end tag may be omitted if there is no more content in the parent element
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'OPTION' && $endtag != 'OPTION' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('OPTION', $a, $i);
								$closed = true;
							}
						}
						/* -- TABLES -- */
						// Check for Table tags where HTML specifies optional end tags,
						if ($endtag == 'TABLE') {
							if ($this->lastoptionaltag == 'THEAD' || $this->lastoptionaltag == 'TBODY' || $this->lastoptionaltag == 'TFOOT') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
							}
							if ($this->lastoptionaltag == 'TR') {
								$this->tag->CloseTag('TR', $a, $i);
							}
							if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
								$this->tag->CloseTag('TR', $a, $i);
							}
						}
						if ($endtag == 'THEAD' || $endtag == 'TBODY' || $endtag == 'TFOOT') {
							if ($this->lastoptionaltag == 'TR') {
								$this->tag->CloseTag('TR', $a, $i);
							}
							if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
								$this->tag->CloseTag('TR', $a, $i);
							}
						}
						if ($endtag == 'TR') {
							if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
							}
						}
						/* -- END TABLES -- */
					}


					// mPDF 6
					if ($this->blk[$this->blklvl]['hide']) {
						if (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags)) {
							unset($this->blk[$this->blklvl]);
							$this->blklvl--;
						}
						continue;
					}

					// mPDF 6
					$this->tag->CloseTag($endtag, $a, $i); // mPDF 6
				} else { // OPENING TAG
					if ($this->blk[$this->blklvl]['hide']) {
						if (strpos($e, ' ')) {
							$te = strtoupper(substr($e, 0, strpos($e, ' ')));
						} else {
							$te = strtoupper($e);
						}
						// mPDF 6
						if ($te == 'THEAD' || $te == 'TBODY' || $te == 'TFOOT' || $te == 'TR' || $te == 'TD' || $te == 'TH') {
							$this->lastoptionaltag = $te;
						}
						if (in_array($te, $this->outerblocktags) || in_array($te, $this->innerblocktags)) {
							$this->blklvl++;
							$this->blk[$this->blklvl]['hide'] = true;
							$this->blk[$this->blklvl]['tag'] = $te; // mPDF 6
						}
						continue;
					}

					/* -- CSS-POSITION -- */
					if ($this->inFixedPosBlock) {
						if (strpos($e, ' ')) {
							$te = strtoupper(substr($e, 0, strpos($e, ' ')));
						} else {
							$te = strtoupper($e);
						}
						$this->fixedPosBlock .= '<' . $e . '>';
						if (in_array($te, $this->outerblocktags) || in_array($te, $this->innerblocktags)) {
							$this->fixedPosBlockDepth++;
						}
						continue;
					}
					/* -- END CSS-POSITION -- */
					$regexp = '|=\'(.*?)\'|s'; // eliminate single quotes, if any
					$e = preg_replace($regexp, "=\"\$1\"", $e);
					// changes anykey=anyvalue to anykey="anyvalue" (only do this inside [some] tags)
					if (substr($e, 0, 10) != 'pageheader' && substr($e, 0, 10) != 'pagefooter' && substr($e, 0, 12) != 'tocpagebreak' && substr($e, 0, 10) != 'indexentry' && substr($e, 0, 8) != 'tocentry') { // mPDF 6  (ZZZ99H)
						$regexp = '| (\\w+?)=([^\\s>"]+)|si';
						$e = preg_replace($regexp, " \$1=\"\$2\"", $e);
					}

					$e = preg_replace('/ (\\S+?)\s*=\s*"/i', " \\1=\"", $e);

					// Fix path values, if needed
					$orig_srcpath = '';
					if ((stristr($e, "href=") !== false) or ( stristr($e, "src=") !== false)) {
						$regexp = '/ (href|src)\s*=\s*"(.*?)"/i';
						preg_match($regexp, $e, $auxiliararray);
						if (isset($auxiliararray[2])) {
							$path = $auxiliararray[2];
						} else {
							$path = '';
						}
						if (trim($path) != '' && !(stristr($e, "src=") !== false && substr($path, 0, 4) == 'var:') && substr($path, 0, 1) != '@') {
							$path = htmlspecialchars_decode($path); // mPDF 5.7.4 URLs
							$orig_srcpath = $path;
							$this->GetFullPath($path);
							$regexp = '/ (href|src)="(.*?)"/i';
							$e = preg_replace($regexp, ' \\1="' . $path . '"', $e);
						}
					}//END of Fix path values
					// Extract attributes
					$contents = [];
					$contents1 = [];
					$contents2 = [];
					// Changed to allow style="background: url('bg.jpg')"
					// Changed to improve performance; maximum length of \S (attribute) = 16
					// Increase allowed attribute name to 32 - cutting off "toc-even-header-name" etc.
					preg_match_all('/\\S{1,32}=["][^"]*["]/', $e, $contents1);
					preg_match_all('/\\S{1,32}=[\'][^\']*[\']/i', $e, $contents2);

					$contents = array_merge($contents1, $contents2);
					preg_match('/\\S+/', $e, $a2);
					$tag = (isset($a2[0]) ? strtoupper($a2[0]) : '');
					$attr = [];
					if ($orig_srcpath) {
						$attr['ORIG_SRC'] = $orig_srcpath;
					}
					if (!empty($contents)) {
						foreach ($contents[0] as $v) {
							// Changed to allow style="background: url('bg.jpg')"
							if (preg_match('/^([^=]*)=["]?([^"]*)["]?$/', $v, $a3) || preg_match('/^([^=]*)=[\']?([^\']*)[\']?$/', $v, $a3)) {
								if (strtoupper($a3[1]) == 'ID' || strtoupper($a3[1]) == 'CLASS') { // 4.2.013 Omits STYLE
									$attr[strtoupper($a3[1])] = trim(strtoupper($a3[2]));
								} // includes header-style-right etc. used for <pageheader>
								elseif (preg_match('/^(HEADER|FOOTER)-STYLE/i', $a3[1])) {
									$attr[strtoupper($a3[1])] = trim(strtoupper($a3[2]));
								} else {
									$attr[strtoupper($a3[1])] = trim($a3[2]);
								}
							}
						}
					}
					$this->tag->OpenTag($tag, $attr, $a, $i); // mPDF 6
					/* -- CSS-POSITION -- */
					if ($this->inFixedPosBlock) {
						$this->fixedPosBlockBBox = [$tag, $attr, $this->x, $this->y];
						$this->fixedPosBlock = '';
						$this->fixedPosBlockDepth = 1;
					}
					/* -- END CSS-POSITION -- */
					if (preg_match('/\/$/', $e)) {
						$this->tag->CloseTag($tag, $a, $i);
					}
				}
			} // end TAG
		} // end of	foreach($a as $i=>$e)

		if ($close) {
			// Close any open block tags
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $a, $i);
			}

			// Output any text left in buffer
			if (count($this->textbuffer) && !$parseonly) {
				$this->printbuffer($this->textbuffer);
			}
			if (!$parseonly) {
				$this->textbuffer = [];
			}

			/* -- CSS-FLOAT -- */
			// If ended with a float, need to move to end page
			$currpos = $this->page * 1000 + $this->y;
			if (isset($this->blk[$this->blklvl]['float_endpos']) && $this->blk[$this->blklvl]['float_endpos'] > $currpos) {
				$old_page = $this->page;
				$new_page = intval($this->blk[$this->blklvl]['float_endpos'] / 1000);
				if ($old_page != $new_page) {
					$s = $this->PrintPageBackgrounds();
					// Writes after the marker so not overwritten later by page background etc.
					$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->pages[$this->page]);
					$this->pageBackgrounds = [];
					$this->page = $new_page;
					$this->ResetMargins();
					$this->Reset();
					$this->pageoutput[$this->page] = [];
				}
				$this->y = (($this->blk[$this->blklvl]['float_endpos'] * 1000) % 1000000) / 1000; // mod changes operands to integers before processing
			}
			/* -- END CSS-FLOAT -- */

			/* -- CSS-IMAGE-FLOAT -- */
			$this->printfloatbuffer();
			/* -- END CSS-IMAGE-FLOAT -- */

			// Create Internal Links, if needed
			if (!empty($this->internallink)) {
				foreach ($this->internallink as $k => $v) {
					if (strpos($k, "#") !== false) {
						continue;
					} // ignore
					$ypos = $v['Y'];
					$pagenum = $v['PAGE'];
					$sharp = "#";
					while (array_key_exists($sharp . $k, $this->internallink)) {
						$internallink = $this->internallink[$sharp . $k];
						$this->SetLink($internallink, $ypos, $pagenum);
						$sharp .= "#";
					}
				}
			}

			$this->bufferoutput = false;

			/* -- CSS-POSITION -- */
			if (count($this->fixedPosBlockSave) && $sub != 4) {
				foreach ($this->fixedPosBlockSave as $fpbs) {
					$old_page = $this->page;
					$this->page = $fpbs[2];
					$this->WriteFixedPosHTML($fpbs[0], 0, 0, 100, 100, 'auto', $fpbs[1]);  // 0,0,10,10 are overwritten by bbox
					$this->page = $old_page;
				}
				$this->fixedPosBlockSave = [];
			}
			/* -- END CSS-POSITION -- */
		}
	}

	function _getHtmlHeight($html)
	{
		$save_state = $this->state;
		if ($this->state == 0) {
			$this->AddPage($this->CurOrientation);
		}
		$this->state = 2;
		$this->Reset();
		$this->pageoutput[$this->page] = [];
		$save_x = $this->x;
		$save_y = $this->y;
		$this->x = $this->lMargin;
		$this->y = $this->margin_header;
		$html = str_replace('{PAGENO}', $this->pagenumPrefix . $this->docPageNum($this->page) . $this->pagenumSuffix, $html);
		$html = str_replace($this->aliasNbPgGp, $this->nbpgPrefix . $this->docPageNumTotal($this->page) . $this->nbpgSuffix, $html);
		$html = str_replace($this->aliasNbPg, $this->page, $html);
		$html = preg_replace_callback('/\{DATE\s+(.*?)\}/', [$this, 'date_callback'], $html); // mPDF 5.7
		$this->HTMLheaderPageLinks = [];
		$this->HTMLheaderPageAnnots = [];
		$this->HTMLheaderPageForms = [];
		$savepb = $this->pageBackgrounds;
		$this->writingHTMLheader = true;
		$this->WriteHTML($html, 4); // parameter 4 saves output to $this->headerbuffer
		$this->writingHTMLheader = false;
		$h = ($this->y - $this->margin_header);
		$this->Reset();
		// mPDF 5.7.2 - Clear in case Float used in Header/Footer
		$this->blk[0]['blockContext'] = 0;
		$this->blk[0]['float_endpos'] = 0;

		$this->pageoutput[$this->page] = [];
		$this->headerbuffer = '';
		$this->pageBackgrounds = $savepb;
		$this->x = $save_x;
		$this->y = $save_y;
		$this->state = $save_state;
		if ($save_state == 0) {
			unset($this->pages[1]);
			$this->page = 0;
		}
		return $h;
	}

	function SetHTMLHeader($header = '', $OE = '', $write = false)
	{

		$height = 0;
		if (is_array($header) && isset($header['html']) && $header['html']) {
			$Hhtml = $header['html'];
			if ($this->setAutoTopMargin) {
				if (isset($header['h'])) {
					$height = $header['h'];
				} else {
					$height = $this->_getHtmlHeight($Hhtml);
				}
			}
		} elseif (!is_array($header) && $header) {
			$Hhtml = $header;
			if ($this->setAutoTopMargin) {
				$height = $this->_getHtmlHeight($Hhtml);
			}
		} else {
			$Hhtml = '';
		}

		if ($OE !== 'E') {
			$OE = 'O';
		}

		if ($OE === 'E') {
			if ($Hhtml) {
				$this->HTMLHeaderE = [];
				$this->HTMLHeaderE['html'] = $Hhtml;
				$this->HTMLHeaderE['h'] = $height;
			} else {
				$this->HTMLHeaderE = '';
			}
		} else {
			if ($Hhtml) {
				$this->HTMLHeader = [];
				$this->HTMLHeader['html'] = $Hhtml;
				$this->HTMLHeader['h'] = $height;
			} else {
				$this->HTMLHeader = '';
			}
		}

		if (!$this->mirrorMargins && $OE == 'E') {
			return;
		}
		if ($Hhtml == '') {
			return;
		}

		if ($this->setAutoTopMargin == 'pad') {
			$this->tMargin = $this->margin_header + $height + $this->orig_tMargin;
			if (isset($this->saveHTMLHeader[$this->page][$OE]['mt'])) {
				$this->saveHTMLHeader[$this->page][$OE]['mt'] = $this->tMargin;
			}
		} elseif ($this->setAutoTopMargin == 'stretch') {
			$this->tMargin = max($this->orig_tMargin, $this->margin_header + $height + $this->autoMarginPadding);
			if (isset($this->saveHTMLHeader[$this->page][$OE]['mt'])) {
				$this->saveHTMLHeader[$this->page][$OE]['mt'] = $this->tMargin;
			}
		}
		if ($write && $this->state != 0 && (($this->mirrorMargins && $OE == 'E' && ($this->page) % 2 == 0) || ($this->mirrorMargins && $OE != 'E' && ($this->page) % 2 == 1) || !$this->mirrorMargins)) {
			$this->writeHTMLHeaders();
		}
	}
}

?>
