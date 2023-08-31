<?php

namespace nge;

class htmlToWord extends module
{
    public $docFile = '';
    public $title = '';
    public $htmlHead = '';
    public $htmlBody = '';

    function __construct() {
        $this->title = '';
        $this->htmlHead = '';
        $this->htmlBody = '';
    }

    function setDocFileName($docfile) {
        $this->docFile=$docfile;
        if(!preg_match("/\.doc$/i",$this->docFile) && !preg_match("/\.docx$/i",$this->docFile)) {
            $this->docFile.='.doc';
        }
        return;
    }

    function setTitle($title) {
        $this->title=$title;
    }

    function getHeader() {
        $return=<<<EOH
        <htmlxmlns:v="urn:schemas-microsoft-com:vml"
        xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:w="urn:schemas-microsoft-com:office:word"
        xmlns="http://www.w3.org/TR/REC-html40">

        <head>
        <metahttp-equiv=Content-Typecontent="text/html;charset=utf-8">
        <metaname=ProgIdcontent=Word.Document>
        <metaname=Generatorcontent="MicrosoftWord9">
        <metaname=Originatorcontent="MicrosoftWord9">
        <!--[if!mso]>
        <style>
        v\:*{behavior:url(#default#VML);}
        o\:*{behavior:url(#default#VML);}
        w\:*{behavior:url(#default#VML);}
        .shape{behavior:url(#default#VML);}
        </style>
        <![endif]-->
        <title>$this->title</title>
        <!--[ifgtemso9]><xml>
        <w:WordDocument>
        <w:View>Print</w:View>
        <w:DoNotHyphenateCaps/>
        <w:PunctuationKerning/>
        <w:DrawingGridHorizontalSpacing>9.35pt</w:DrawingGridHorizontalSpacing>
        <w:DrawingGridVerticalSpacing>9.35pt</w:DrawingGridVerticalSpacing>
        </w:WordDocument>
        </xml><![endif]-->
        <style>
        <!--
        /*FontDefinitions*/
        @font-face
        {font-family:Verdana;
        panose-1:21164354424;
        mso-font-charset:0;
        mso-generic-font-family:swiss;
        mso-font-pitch:variable;
        mso-font-signature:5368715590004150;}
        /*StyleDefinitions*/
        p.MsoNormal,li.MsoNormal,div.MsoNormal
        {mso-style-parent:"";
        margin:0in;
        margin-bottom:.0001pt;
        mso-pagination:widow-orphan;
        font-size:7.5pt;
        mso-bidi-font-size:8.0pt;
        font-family:"Verdana";
        mso-fareast-font-family:"Verdana";}
        p.small
        {mso-style-parent:"";
        margin:0in;
        margin-bottom:.0001pt;
        mso-pagination:widow-orphan;
        font-size:1.0pt;
        mso-bidi-font-size:1.0pt;
        font-family:"Verdana";
        mso-fareast-font-family:"Verdana";}
        @pageSection1
        {size:8.5in11.0in;
        margin:1.0in1.25in1.0in1.25in;
        mso-header-margin:.5in;
        mso-footer-margin:.5in;
        mso-paper-source:0;}
        div.Section1
        {page:Section1;}
        -->
        </style>
        <!--[ifgtemso9]><xml>
        <o:shapedefaultsv:ext="edit"spidmax="1032">
        <o:colormenuv:ext="edit"strokecolor="none"/>
        </o:shapedefaults></xml><![endif]--><!--[ifgtemso9]><xml>
        <o:shapelayoutv:ext="edit">
        <o:idmapv:ext="edit"data="1"/>
        </o:shapelayout></xml><![endif]-->
        $this->htmlHead
        </head>
        <body>
EOH;
        return$return;
    }

    function getFotter() {
        return"</body></html>";
    }

    function createDoc($html, $file, $download=false) {

        $this->parseHtml($html);
        $this->setDocFileName($file);
        $doc = $this->getHeader();
        $doc .= $this->htmlBody;
        $doc .= $this->getFotter();

        if($download){
            header("Content-Type: application/vnd.ms-word");
            header( "Content-Disposition: attachment; filename=".basename($this->docFile));
            header( "Content-Description: File Transfer");
            while (ob_get_level()) ob_end_clean();

		    @readfile($this->docFile);

            echo $html;

            return true;
        }else{
            return $this->writeFile($this->docFile, $html);
        }
    }

    function parseHtml($html) {
        $html = preg_replace("/<!DOCTYPE((.|\n)*?)>/ims","",$html);
        $html = preg_replace("/<script((.|\n)*?)>((.|\n)*?)<\/script>/ims","",$html);
        preg_match("/<head>((.|\n)*?)<\/head>/ims",$html,$matches);
        $head =! empty($matches[1]) ? $matches[1] : '';
        preg_match("/<title>((.|\n)*?)<\/title>/ims",$head,$matches);
        $this->title=!empty($matches[1])?$matches[1]:'';
        $html = preg_replace("/<head>((.|\n)*?)<\/head>/ims","",$html);
        $head = preg_replace("/<title>((.|\n)*?)<\/title>/ims","",$head);
        $head = preg_replace("/<\/?head>/ims","",$head);
        $html = preg_replace("/<\/?body((.|\n)*?)>/ims","",$html);
        $this->htmlHead = $head;
        $this->htmlBody = $html;
        return;
    }

    function writeFile($file,$content,$mode="w") {
        $fp = @fopen($file,$mode);
        if(!is_resource($fp)) {
            return false;
        }
        fwrite($fp,$content);
        fclose($fp);
        return true;
    }
}