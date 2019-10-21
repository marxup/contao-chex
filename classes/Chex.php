<?php
namespace marxup\chex\classes;

class Chex extends \Backend
{
	protected $_strLicense = null;
	
	protected function apirequest($strAction, $arrParams = [])
	{
		$arrResult = ['error' => 500, 'errormessage' => 'Could not reach server'];
		
		
		// add action and license
		$arrParams['action'] = $strAction;
		if (!empty($this->_strLicense))
			$arrParams['lic'] = $this->_strLicense;
		
		$strUrl = 'https://backups.marxup.de/api/beta.php?' . http_build_query($arrParams);
        
		$strContent = @file_get_contents($strUrl, false);
		$arrTempResult = @json_decode($strContent, true);
		
		// no or invalid data
		if (!$strContent || !isset($arrTempResult['error']))
		{
			$this->log('Chex: Could not read from URL ' . $this->url, 'Chex: Generate backend', TL_ERROR);
			$arrResult['errormessage'] = $GLOBALS['TL_LANG']['marxup_chex']['httperror'];
		}
		else
		{
			// had error
			if ($arrTempResult['error'] > 0)
			{
				switch($arrTempResult['error'])
				{
					// invalid license
					case 403:
						$arrResult['errormessage'] = $GLOBALS['TL_LANG']['marxup_chex']['invalidLicense'];
				}
			}
			else
			{
				$arrResult = $arrTempResult;
			}			
		}
		return $arrResult;
	}
    public function generate()
    {
		// set new license key
		if (!is_null(\Input::post('FORM_SUBMIT')))
		{
			
			if (!is_null(\Input::post('addlicense')))
			{
				$this->_strLicense = \Input::post('licensekey');
				\Config::persist('chex-license', $this->_strLicense);
				\Message::addConfirmation($GLOBALS['TL_LANG']['marxup_chex']['licenseSaved']);
			}
			elseif (!is_null(\Input::post('removelicense')))
			{
				$this->_strLicense = '';
				\Config::remove('chex-license');
				\Message::addConfirmation($GLOBALS['TL_LANG']['marxup_chex']['licenseRemoved']);
			}
		}
		
		// get key if not set
		if (is_null($this->_strLicense))
			$this->_strLicense = \Config::get('chex-license');

		//!todo mulu: Bestellschritte und co
		if (empty($this->_strLicense))
		{
			$objTemplate = new \Contao\BackendTemplate('nolicense');
		}
		else
		{
			// debug
			if (!empty($_GET['lic']))
				$this->_strLicense = $_GET['lic'];
			
			$objTemplate = new \Contao\BackendTemplate('status');
			
			// start a manual backup
			if (!empty($_POST['newmanualbackup']))
			{
				$this->apirequest('manualbackup');
			}
			
			$arrStatus = $this->apirequest('info');
			
			if ($arrStatus['error'] > 0)
				\Message::addError($arrStatus['errormessage']);
			
			$objTemplate->status = $arrStatus;
		}

		$objTemplate->license = $license;
		$objTemplate->lang	  = $GLOBALS['TL_LANG']['marxup_chex'];
		$objTemplate->messages = \Message::generate();
        return $objTemplate->parse();
    }
}