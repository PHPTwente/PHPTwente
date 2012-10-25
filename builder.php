<?php
class WebsiteBuilder {

	public $m_sPagesDirectory = 'pages';

	public $m_sTemplateFile = 'index.html';

	function __construct()
	{
		$this->m_oDocument = new DOMDocument();
	}

	public function run()
	{
		$oDocument = $this->m_oDocument;

		$aFiles  = $this->retrievePageFiles();

		$iLength = strlen($this->m_sPagesDirectory) + 1;

		$sTemplateFile = file_get_contents($this->m_sTemplateFile);

		/* Ignoring errors as HTML5 tags trigger them */
		libxml_use_internal_errors(true);
		$oDocument->loadHTML($sTemplateFile);
		libxml_clear_errors();

		$oContentElement = $oDocument->getElementById('main-content');

		foreach($aFiles as $t_sFilePath)
		{
			$sFileContents = $this->retrieveFileContent($t_sFilePath);

			if(!empty($sFileContents))
			{
				$sFileName = substr($t_sFilePath, $iLength);

				$this->insertPageIntoTemplate($sFileContents, $oContentElement);
				$this->activateMenuItem($sFileName);

				$bSaved = $oDocument->saveHTMLFile($sFileName);

				echo  '<li>Saving "' . $t_sFilePath
					. '" to "' . $sFileName . '" '
					. ($bSaved?'succeded':'failed')
				;
			}
		}
	}

	protected function insertPageIntoTemplate($p_sFileContents,
		DOMElement $p_oContentElement)
	{
		$oFragment = $this->m_oDocument->createDocumentFragment();
		$oFragment->appendXML($p_sFileContents);

		while ($p_oContentElement->hasChildNodes())
		{
			$p_oContentElement->removeChild($p_oContentElement->firstChild);
		}

		$p_oContentElement->appendChild($oFragment);
	}

	protected function retrievePageFiles()
	{
		$aFiles = glob('' . $this->m_sPagesDirectory . '/*.html');
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
		foreach($NodeList as $oElement)
		{
			/**@var $oElement DOMElement */
			if($this->isActiveItem($oElement))
			{
				$oElement->setAttribute('class', '');
			}
			elseif($this->linksToFile($oElement, $p_sFileName))
			{
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
		return ($p_oElement->getAttribute('href') === '/' . $p_sFileName);
	}
}

$oWebsiteBuilder = new WebsiteBuilder();
$oWebsiteBuilder->run();

#EOF