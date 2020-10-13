<?php

namespace ofxtocsv;
require_once('OfxParser.php');

class Parser
{
    public function loadFromFile($ofxFile)
    {
        if (!file_exists($ofxFile)) {
            throw new \InvalidArgumentException("File '{$ofxFile}' could not be found");
        }

        return $this->loadFromString(file_get_contents($ofxFile));
    }
    public function loadFromString($ofxContent)
    {
        $ofxContent = utf8_encode($ofxContent);
        $ofxContent = $this->conditionallyAddNewlines($ofxContent);
        
        //echo $ofxContent."\n";
        $sgmlStart = stripos($ofxContent, '<OFX>');
        $ofxSgml = trim(substr($ofxContent, $sgmlStart));
        if($this->xmlLoadString($ofxSgml)){
            $xml = $this->xmlLoadString($ofxSgml);
        }else{
            $ofxXml = $this->convertSgmlToXml($ofxSgml);
            $xml = $this->xmlLoadString($ofxXml);
        }
        if(!$xml){
            return false;
        }
        return new OfxParser($xml);
    }

    private function conditionallyAddNewlines($ofxContent)
    {
        if (preg_match('/<OFX>.*<\/OFX>/', $ofxContent) === 1) {
            return str_replace('<', "\n<", $ofxContent); // add line breaks to allow XML to parse
        }

        return $ofxContent;
    }

    private function xmlLoadString($xmlString)
    {
        //echo $xmlString;
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);
        if ($errors = libxml_get_errors()) {
            // echo "error : Failed to parse OFX:". var_export($errors, true);
            return false;
            //throw new \RuntimeException('Failed to parse OFX: ' . var_export($errors, true));
        }

        return $xml;
    }

    private function closeUnclosedXmlTags($line)
    {
        if (preg_match(
            "/<([A-Za-z0-9.]+)>([\wà-úÀ-Ú0-9\.\-\_\+\, ;:\[\]\'\&\/\\\*\(\)\+\{\|\}\!\£\$\?=@€£#%±§~`\"]+)$/",
            trim($line),
            $matches
        )) {
            //echo "test : <{$matches[1]}>{$matches[2]}</{$matches[1]}>\n";
            return "<{$matches[1]}>{$matches[2]}</{$matches[1]}>";
        }
        //echo "hiii\n";
        return $line;
    }
    private function convertSgmlToXml($sgml)
    {
        $sgml = str_replace(["\r\n", "\r"], "\n", $sgml);

        $sgml = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $sgml);

        $lines = explode("\n", $sgml);

        $xml = '';
        foreach ($lines as $line) {
            $xml .= trim($this->closeUnclosedXmlTags($line)) . "\n";
        }
        //echo $xml;
        return trim($xml);
    }
}
