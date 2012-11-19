<?php
// get the config.php for environment vars and database access
include_once('../../../wp-config.php');

function run_dir($directory, $dynamic_type = ".php", $disable_dynamic_files_caching = false, $ignored_files = '.less|.scss|.sass' , $verbose = FALSE){
	global $run_dir;
	$helper = '';
	$lastFileWasDynamic = FALSE;
	if(!empty($ignored_files)){
		$ignored_files = '.less|.scss|.sass|'.$ignored_files;	
	}else{
		$ignored_files = '.less|.scss|.sass';
	}	
	$dir = new RecursiveDirectoryIterator($directory);
	if($verbose){echo "\n#recursively crawling through folder: ".$directory."\n";
	}
		
	foreach(new RecursiveIteratorIterator($dir) as $file) 
	{
		
		if ($file->IsFile() && substr($file->getFilename(), 0, 1) != ".") 
		{
			if(preg_match('/'.$dynamic_type.'$/', $file)) {
				// DYNAMIC FILE
				if(!$lastFileWasDynamic) 
				{
					if($disable_dynamic_files_caching){
						
					}else{
						//echo "\nNETWORK:\n";
					}
				}
				$lastFileWasDynamic = TRUE;
			} 
			else // NO DYNAMIC FILE 
			{
				if($lastFileWasDynamic) 
				{
					//echo "\nCACHE:\n";
					$lastFileWasDynamic = FALSE;
				}				
			}
				// ENABLED DYNAMIC FILE CACHING (default)
				if(preg_match('/'.$ignored_files.'$/', $file)){
					//dont output filename
				}else{
					if($disable_dynamic_files_caching){
						$filename = substr($file, -3, 3);
						if($filename == 'php'){
							//do nothing if php file
							continue;
						}
					}
					//echo $file . "\n";
					if($lastFileWasDynamic){
						$run_dir['dynamic'][] = $file;
					}else{
						$run_dir['cache'][] = $file;
					}
				}
			/*} */			
		}
	}
}?>