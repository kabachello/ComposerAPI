<?php namespace kabachello\ComposerAPI;

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

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
	
	protected function get_default_output_container(){
		// Setup composer output formatter
		$stream = fopen('php://temp', 'w+');
		$output = new StreamOutput($stream);
		return $output;
	}
	
	protected function call_command($command, array $arguments = null, OutputInterface $output = null){
		if (is_null($output)){
			$output = $this->get_default_output_container();
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
		$input_array = array('command' => $command);
		if (!is_null($arguments)){
			$input_array += $arguments;
		}
		$code = $application->run(new ArrayInput($input_array), $output);
		
		// Restore environment
		chdir($dir);
		set_time_limit($time_limit);
		ini_set('memory_limit', $memory_limit);
		error_reporting($error_reporting_level);
		
		return $output;
	}
	
	/**
	 * 
	 * @param array|string $package_names
	 * @return array
	 */
	protected function prepare_array_input_args(array $arguments = null){
		$result = null;
		if (is_null($arguments) || count($arguments) == 0){
			return null;
		} else {
			foreach ($arguments as $arg){
				$result[$arg] = true;
			}
		}
		return $result;
	}
	
	/**
	 * Reads the composer.json file from the current directory, resolves the dependencies, and installs them: see https://getcomposer.org/doc/03-cli.md#install
	 * 
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function install(array $options = null, OutputInterface $output = null) {
		return $this->call_command('install', $this->prepare_array_input_args($options), $output);
	}
	
	/**
	 * Updates dependencies to the latest possible version and updates the composer.lock file: see https://getcomposer.org/doc/03-cli.md#update
	 * E.g. $composer->update() will update all dependencies while $composer->update(array('symfony/css-crawler'), array('--optimize-autoloader')) 
	 * will update only the CSS crawler symfony component optimizing the autoloader afterwards.
	 * 
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function update(array $package_names = null, array $options = null, OutputInterface $output = null) {
		$arguments = array();
		
		if (is_array($package_names) && count($package_names) > 0){
			$arguments['packages'] = $package_names;
		}
		
		if ($options = $this->prepare_array_input_args($options)){
			$arguments += $options;
		}
		
		return $this->call_command('update', $arguments, $output);
	}
	
	/**
	 * Installes one or more packages: see https://getcomposer.org/doc/03-cli.md#require.
	 * E.g. $composer->require(array('monolog/monolog:~1.16', 'slim/slim')) will install monolog in version 1.16
	 * or later and the Slim framework in the latest available version.
	 * 
	 * @param array $package_names
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function require(array $package_names, array $options = null, OutputInterface $output = null){
		$arguments = array();
		
		if (is_array($package_names) && count($package_names) > 0){
			$arguments['packages'] = $package_names;
		}
				
		if ($options = $this->prepare_array_input_args($options)){
			$arguments += $options;
		}
		
		return $this->call_command('require', $arguments, $output);
	}
	
	/**
	 * Uninstalles one or more packages: see https://getcomposer.org/doc/03-cli.md#remove.
	 * E.g. $composer->remove(array('monolog/monolog', 'slim/slim')) will uninstall monolog in version 1.16
	 * or later and the Slim framework in the latest available version.
	 * 
	 * @param array $package_names
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function remove(array $package_names, array $options = null, OutputInterface $output = null){
		$arguments = array();
	
		if (is_array($package_names) && count($package_names) > 0){
			$arguments['packages'] = $package_names;
		}
	
		if ($options = $this->prepare_array_input_args($options)){
			$arguments += $options;
		}
	
		return $this->call_command('remove', $arguments, $output);
	}
	
	/**
	 * Search through the current project's package repositories: see https://getcomposer.org/doc/03-cli.md#search
	 * 
	 * @param array $search_terms
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function search(array $search_terms, array $options = null, OutputInterface $output = null) {
		$arguments = array();
	
		if (is_array($search_terms) && count($search_terms) > 0){
			$arguments['packages'] = $search_terms;
		}
	
		if ($options = $this->prepare_array_input_args($options)){
			$arguments += $options;
		}
	
		return $this->call_command('search', $arguments, $output);
	}
	
	/**
	 * Lists all installed packages: see https://getcomposer.org/doc/03-cli.md#show.
	 * E.g. $composer->show(array('--latest')) will show all packages with the respective latest version available
	 *
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function show(array $options = null, OutputInterface $output = null) {
		return $this->call_command('show', $this->prepare_array_input_args($options), $output);
	}
	
	/**
	 * Shows a list of installed packages that have updates available, including their current and latest versions: see https://getcomposer.org/doc/03-cli.md#outdated
	 * E.g. $composer->outdated() will list only outdated packages while $composer->outdated(array('--all')) will list alle packages
	 * installed with the respective latest versions.
	 * 
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function outdated(array $options = null, OutputInterface $output = null) {
		return $this->call_command('outdated', $this->prepare_array_input_args($options), $output);
	}
	
	/**
	 * Lists all packages suggested by currently installed set of packages: see https://getcomposer.org/doc/03-cli.md#suggests.
	 * E.g. $composer->suggests(array('monolog/monolog', 'slim/slim'), array('--by-package')) will show packages
	 * suggested by monolog and Slim grouped by package 
	 * 
	 * @param array $package_names
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function suggests(array $package_names = null, array $options = null, OutputInterface $output = null){
		$arguments = array();
	
		if (is_array($package_names) && count($package_names) > 0){
			$arguments['packages'] = $package_names;
		}
	
		if ($options = $this->prepare_array_input_args($options)){
			$arguments += $options;
		}
	
		return $this->call_command('suggests', $arguments, $output);
	}
	
	/**
	 * Tells you which other packages depend on a certain package: see https://getcomposer.org/doc/03-cli.md#depends.
	 * E.g. $composer->depends('doctrine/lexer', array('--tree')) will show packages, that require the doctrine lexer
	 * with all subrequirements in the form of a tree.
	 * 
	 * @param string $package_name
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function depends($package_name, $version = null, array $options = null, OutputInterface $output = null){
		$arguments = array();
	
		$arguments['package'] = $package_name;
		
		if (!is_null($version)){
			$arguments['constraint'] = $version;
		}
	
		if ($options = $this->prepare_array_input_args($options)){
			$arguments += $options;
		}
	
		return $this->call_command('depends', $arguments, $output);
	}
	
	/**
	 * Tells you which packages are blocking a given package from being installed: see https://getcomposer.org/doc/03-cli.md#prohibits.
	 * E.g. $composer->prohibits('symfony/symfony', '3.1', array('--tree')) will show packages in your current project,
	 * that would prevent the installation of Symfony 3.1 with all their subrequirements in the form of a tree.
	 *
	 * @param string $package_name
	 * @param string $verion
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function prohibits($package_name, $version = null, array $options = null, OutputInterface $output = null){
		$arguments = array();
	
		$arguments['package'] = $package_name;
		
		if (!is_null($version)){
			$arguments['constraint'] = $version;
		}
	
		if ($options = $this->prepare_array_input_args($options)){
			$arguments += $options;
		}
	
		return $this->call_command('prohibits', $arguments, $output);
	}
	
	/**
	 * Validates the composer.json file: see https://getcomposer.org/doc/03-cli.md#validate
	 *
	 * @param array $options
	 * @param OutputInterface $output
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function validate(array $options = null, OutputInterface $output = null) {
		return $this->call_command('validate', $this->prepare_array_input_args($options), $output);
	}

}
?>