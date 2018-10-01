<?php

class Creare_CreareSeoCore_Model_Cleanup extends Mage_Core_Model_Abstract
{
	
	protected $errors = array();
	
	public function docleanup()
	{	
		if(Mage::getStoreConfig('creareseocore/cleanup/cachefolder')){
			$this->cleanVar();
			$this->cleanCache();
		}
		
		if(Mage::getStoreConfig('creareseocore/cleanup/logdb')){
			$this->cleanDb();
		}
		
		if(Mage::getStoreConfig('creareseocore/cleanup/enablecache')){
			$this->enableCache();
		}
		
		if(count($this->errors) > 0){
			$this->sendEmail();
		} else {
			Mage::getConfig()->saveConfig('creareseocore/cleanup/success', date('Y-m-d, H:i:s'));
			Mage::getConfig()->reinit();
			Mage::app()->reinitStores();	
		}
		
	}
	
	protected function enableCache()
	{
            $model = Mage::getModel('core/cache');
            $options = $model->canUse('');

            foreach($options as $option=>$value) {
                    if ($options[$option] != 1)
                    {
                            $options[$option] = 1;
                    }
            }

            $model->saveOptions($options);
	}
	
	protected function cleanDb()
	{
		$tables = array(
			'dataflow_batch_export',
			'dataflow_batch_import',
			/*'log_customer', removed this table as it clears the useful 'Last logged in' customer data */
			'log_quote',
			'log_summary',
			'log_summary_type',
			'log_url',
			'log_url_info',
			'log_visitor',
			'log_visitor_info',
			'log_visitor_online',
			'report_event'
		);
		
		$db = Mage::getSingleton('core/resource')->getConnection('read');
		$prefix = Mage::getConfig()->getTablePrefix();
		foreach($tables as $table){
			$db->query('TRUNCATE '.$prefix.$table);
		}
		
	}
	
	protected function cleanCache()
	{
            $dir = Mage::getBaseDir()."/var/cache/";
            if(is_dir($dir)){
                    $this->removeContents($dir);
            }
	}
	
	protected function cleanVar()
	{	
            $dir = Mage::getBaseDir().'/var/session/';	
            $this->removeSessionContents($dir);
	}
	
	protected function removeContents($dir)
	{
		
		if(!opendir($dir)){
			$this->errors[] = "Cannot open $dir";	
		}
		$mydir = opendir($dir);
		while(false !== ($file = readdir($mydir))) :
		
			if($file != "." && $file != ".."){
				chmod($dir.$file, 0777);
				if(is_dir($dir.$file)) {
					chdir('.');
					$this->removeContents($dir.$file."/");
					
					if(!rmdir($dir.$file)){
						$this->errors[] = "Could not delete folder $dir$file";
					}
				} else {
					chmod($dir.$file, 0777);
					if(!unlink($dir.$file)){
						$this->errors[] = "Could not delete file $dir$file";
					}
				}
			}
			
		endwhile;
		closedir($mydir);	
	}
	
	protected function removeSessionContents($dir)
	{
		if(!opendir($dir)){
			$this->errors[] = "Cannot open $dir";	
		}
		$mydir = opendir($dir);
		while(false !== ($file = readdir($mydir))) :
		
			if($file != "." && $file != ".."){
				chmod($dir.$file, 0777);
				if(is_dir($dir.$file)) {
					chdir('.');
					$this->removeContents($dir.$file."/");
					
					if(!rmdir($dir.$file)){
						$this->errors[] = "Could not delete folder $dir$file";
					}
				} else {
					chmod($dir.$file, 0777);
					
					// check to make sure that session file is over a week old
					
					$time = filemtime($dir.$file);
					
					if($time < (time() - (7 * 24 * 60 * 60))){
						if(!unlink($dir.$file)){
							$this->errors[] = "Could not delete file $dir$file";
						}
					}
				}
			}
			
		endwhile;
		closedir($mydir);
	}

	public function sendEmail()
	{
		if($email = Mage::getStoreConfig('creareseocore/cleanup/problems')):
		
			$content = "";
			
			foreach($this->errors as $error){
				$content .= $error."\n";	
			}

			if ($content !== "")
			{
				$mail = Mage::getModel('core/email');
				$mail->setToEmail($email);
				$mail->setBody($content);
				$mail->setSubject('Problems with Cleanup');
				$mail->setType('text');

				try {
					$mail->send();
				} catch (Exception $e) {
					Mage::logException($e);
				}
			}
		endif;
	}

}