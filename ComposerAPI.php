<?php namespace kabachello\ComposerAPI;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerAPI {
	private $path_to_composer_home = '';
	private $path_to_composer_json = '';
	private $proxy_http = null;
	private $proxy_https = null;
	private $composer_application = null;
	
	public function __construct($path_to_composer_json){
		$this->set_path_to_composer_json($path_to_composer_json);
	}
	
	public function get_path_to_composer_json() {
		return $this->path_to_composer_json;
	}
	
	public function set_path_to_composer_json($value) {
		$this->path_to_composer_json = $value;
		return $this;
	}
	
	  
	/**
	 * 
	 * @return string|unknown
	 */
	public function get_path_to_composer_home() {
		return $this->path_to_composer_home;
	}
	
	/**
	 * 
	 * @param unknown $value
	 * @return \axenox\PackageManager\ComposerApi
	 */
	public function set_path_to_composer_home($value) {
		$this->path_to_composer_home = $value;
		return $this;
	}  
	
	/**
	 * 
	 * @return unknown
	 */
	public function get_proxy_http() {
		return $this->proxy_http;
	}
	
	/**
	 * 
	 * @param unknown $http_proxy
	 * @param unknown $https_proxy
	 * @return \axenox\PackageManager\ComposerApi
	 */
	public function set_proxy($http_proxy = null, $https_proxy = null) {
		$this->proxy_http = $http_proxy;
		$this->proxy_https = $https_proxy;
		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_proxy_https() {
		return $this->proxy_https;
	}
	
	/**
	 * 
	 * @return \axenox\PackageManager\ComposerApi
	 */
	protected function register_proxy(){
		if ($this->get_proxy_http()){
			$_SERVER['HTTP_PROXY'] = $this->get_proxy_http();
		}
		if ($this->get_proxy_https()){
			$_SERVER['HTTPS_PROXY'] = $this->get_proxy_https();
		}
		return $this;
	}
	    
	/**
	 * 
	 * @return \Composer\Console\Application
	 */
	public function get_composer_application(){
		if ($this->get_path_to_composer_home()){
			putenv('COMPOSER_HOME=' . $this->get_path_to_composer_home());
		}
		$application = new Application();
		$application->setAutoExit(false);
		
		return $application;
	}
	
	protected function get_default_output_formatter(){
		// Setup composer output formatter
		$stream = fopen('php://temp', 'w+');
		$output = new StreamOutput($stream);
		return $output;
	}
	
	protected function call_command($command, array $arguments = null, array $options = null, OutputInterface $output = null){
		if (is_null($output)){
			$output = $this->get_default_output_formatter();
		}
		
		// Switch directory to the composer home folder
		$dir = getcwd();
		chdir($this->get_path_to_composer_json());
		// Extend time and memory limits for the script
		$time_limit = ini_get('max_execution_time');
		set_time_limit(300);
		$memory_limit = ini_get('memory_limit');
		ini_set('memory_limit','1G');
		$error_reporting_level = error_reporting();
		
		
		// Get composer
		$application = $this->get_composer_application();
		// Call command
		$code = $application->run(new ArrayInput(array('command' => $command)), $output);
		
		// Restore environment
		chdir($dir);
		set_time_limit($time_limit);
		ini_set('memory_limit', $memory_limit);
		error_reporting($error_reporting_level);
		
		return $output;
	}
	
	/**
	 * 
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function install(OutputInterface $output = null) {
		return $this->call_command('install', null, null, $output);
	}
	
	public function update(OutputInterface $output = null) {
		return $this->call_command('update', null, null, $output);
	}
	
	public function show(OutputInterface $output = null) {
		return $this->call_command('show', null, null, $output);
	}

}
?>