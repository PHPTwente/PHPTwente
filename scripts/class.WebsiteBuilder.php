<?php
set_error_handler(function ($p_iErrorNumber, $p_sErrorMessage, $p_sErrorFile, $p_sErrorLine ) {
        throw new ErrorException($p_sErrorMessage, $p_iErrorNumber, $p_iErrorNumber, $p_sErrorFile, $p_sErrorLine);
});

class WebsiteBuilder
{
//////////////////////////////// CLASS PROPERTIES \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	public $m_sProjectRoot;

    public $m_sInputDirectory;

    public $m_sOutputDirectory;

    public $m_sTemplateFile;
	
////////////////////////////// SETTERS AND GETTERS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
	public function getProjectRoot()
	{
		return $this->m_sProjectRoot;
	}
	
	public function setProjectRoot($p_sProjectRoot)
	{
		$this->m_sProjectRoot = $p_sProjectRoot;
	}
	
    public function getInputDirectory()
    {
        return $this->m_sInputDirectory;
    }

    public function setInputDirectory($p_sPagesDirectory)
    {
        $this->m_sInputDirectory = $p_sPagesDirectory;
    }

    public function getTemplateFile()
    {
        return $this->m_sTemplateFile;
    }

    public function setTemplateFile($p_sTemplateFile)
    {
        $this->m_sTemplateFile = $p_sTemplateFile;
    }

    public function getOutputDirectory()
    {
        return $this->m_sOutputDirectory;
    }

    public function setOutputDirectory($p_sOutputDirectory)
    {
        $this->m_sOutputDirectory = $p_sOutputDirectory;
    }

////////////////////////////////// PUBLIC API \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    public function __construct()
    {
        $this->m_oDocument = new DOMDocument();
    }

    public function run()
    {
        $sOutput = '';

        $oDocument = $this->m_oDocument;

        $aFiles = $this->retrievePageFiles();

        $iLength = strlen($this->m_sInputDirectory);

        $sTemplateFile = file_get_contents($this->m_sTemplateFile);

        /* Ignoring errors as HTML5 tags trigger them */
        libxml_use_internal_errors(true);
        $oDocument->loadHTML($sTemplateFile);
        libxml_clear_errors();

        $oContentElement = $oDocument->getElementById('main-content');

        $sOutput .= '<ul>';
        foreach ($aFiles as $t_sFilePath)
        {
            $sErrorMessage = '';
            $bSuccess = false;
            $sFileContents = $this->retrieveFileContent($t_sFilePath);

            $sFileName = $this->getOutputDirectory() . '/' . substr($t_sFilePath, $iLength);
            $bSuccess = $this->insertPageIntoTemplate($sFileContents, $oContentElement);

            if($bSuccess === true)
            {
                $this->activateMenuItem($sFileName);
                try {
                    $bSuccess = $oDocument->saveHTMLFile($sFileName);
                } catch (Exception $eFailed){
                    $bSuccess = false;
                    $sErrorMessage = $eFailed->getMessage();
                }#catch
            }
            else
            {
                $oLibXmlError = libxml_get_last_error();
                // $oLibXmlError->level // LIBXML_ERR_WARNING, LIBXML_ERR_ERROR or LIBXML_ERR_FATAL
                // $oLibXmlError->code // @see http://www.xmlsoft.org/html/libxml-xmlerror.html#xmlParserErrors
                $sXmlError = sprintf(
                      'XML Error: %s (%s:%s)'
                    , $oLibXmlError->message
                    , $oLibXmlError->line
                    , $oLibXmlError->column
                );
                $sErrorMessage = 'Could not insert Page into Template' . $sXmlError;
            }#if


            $sStatus = $bSuccess ? 'succeeded' : 'failed';
            $sOutput .= '<li>'
                . '<strong class="' . $sStatus . '">' . $sStatus . '</strong> '
                . 'Saving <span class="filename">' . $t_sFilePath
                . '</span> to <span class="filename">' . $sFileName . '</span> '
                . (empty($sErrorMessage)? '' : '<br /><span class="error-message">'.$sErrorMessage.'</span>')
            ;
        }#foreach
        $sOutput .= '</ul>';

        return $sOutput;
    }

//////////////////////////////// UTILITY METHODS \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    protected function insertPageIntoTemplate(
        $p_sFileContents,DOMElement $p_oContentElement
    )
    {
        $bAppended = false;
        $oFragment = $this->m_oDocument->createDocumentFragment();

        if(!empty($p_sFileContents)){
            $bAppended = $oFragment->appendXML($p_sFileContents);
            if($bAppended === true){

                while ($p_oContentElement->hasChildNodes()) {
                    $p_oContentElement->removeChild($p_oContentElement->firstChild);
                }#while

                $p_oContentElement->appendChild($oFragment);
            }#if
        }#if
        return $bAppended;
    }

    protected function retrievePageFiles()
    {
        $aFiles = array();

        $directoryIterator = new RecursiveDirectoryIterator($this->getInputDirectory());
        $iteratorIterator = new RecursiveIteratorIterator($directoryIterator);

        foreach ($iteratorIterator as $t_sFileInfo){
            $sExtension = pathinfo($t_sFileInfo, PATHINFO_EXTENSION);
            if($sExtension === 'html'){
                $aFiles[] = $t_sFileInfo;
            }
        }

        return $aFiles;
    }

    protected function retrieveFileContent($t_sFilePath)
    {
        $sFileContents = file_get_contents($t_sFilePath);
        $sFileContents = trim($sFileContents);
        return $sFileContents;
    }

    protected function activateMenuItem($p_sFileName)
    {
        $NodeList = $this->m_oDocument->getElementsByTagName('a');
        foreach ($NodeList as $oElement) {
            /**@var $oElement DOMElement */
            if ($this->isActiveItem($oElement)) {
                $oElement->setAttribute('class', '');
            } elseif ($this->linksToFile($oElement, $p_sFileName)) {
                $oElement->setAttribute('class', 'active');
            }
        }
    }

    protected function isActiveItem(DOMElement $p_oNode)
    {
        $sClassName = $p_oNode->getAttribute('class');
        $bActive = strpos($sClassName, 'active') !== false;
        return $bActive;
    }

    protected function linksToFile(DOMElement $p_oElement, $p_sFileName)
    {
		$sHref = $p_oElement->getAttribute('href');
        return ($this->getProjectRoot().$sHref === $p_sFileName)
			|| ($sHref === '/' && $p_sFileName === $this->getOutputDirectory().'/index.html');
    }
}

#EOF