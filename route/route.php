<?php

	class ZF1Plugin extends ZAppsPlugin {
		
		public function resolveMVCEnter($context) {
			
		}
		
		public function resolveMVCLeave($context) {
			if (!$this->resolved) {
				$this->resolved = true;
				
				$Zend_Controller_Dispatcher_Standard = $context["this"];
				$request = $context["functionArgs"][0];
	    
				$action = $Zend_Controller_Dispatcher_Standard->getActionMethod($request);
				$className = $this->getControllerName($Zend_Controller_Dispatcher_Standard, $request);
				$module = $this->getModuleClassName($Zend_Controller_Dispatcher_Standard, $className);
				if (!$action && !$className) {
					return;
				}
				$mvc = array (  'module' => $module,
								'controller' => $className,
								'action' => $action
	                                     
	                         );
				
				$this->setRequestMVC($mvc);				
			}		
		}		
		
		private function getModuleClassName($Zend_Controller_Dispatcher_Standard, $className) {
			$moduleClassName = $className;
		   
			
			$reflection = new \ReflectionProperty('Zend_Controller_Dispatcher_Standard', '_curModule');
			$reflection->setAccessible(true);
			$_curModule = $reflection->getValue($Zend_Controller_Dispatcher_Standard);
		 
			
			$reflection = new \ReflectionProperty('Zend_Controller_Dispatcher_Standard', '_defaultModule');
			$reflection->setAccessible(true);
			$_defaultModule = $reflection->getValue($Zend_Controller_Dispatcher_Standard);
			
			if (($_defaultModule != $_curModule)
					|| $Zend_Controller_Dispatcher_Standard->getParam('prefixDefaultModule'))
			{
				$moduleClassName = $Zend_Controller_Dispatcher_Standard->formatClassName($_curModule, $className);
			}
			return $moduleClassName;
		}
		
		private function getControllerName($Zend_Controller_Dispatcher_Standard, $request) {
			/**
			 * Get controller class
			 */
			if (!$Zend_Controller_Dispatcher_Standard->isDispatchable($request)) {
				$controller = $request->getControllerName();
				if (!$Zend_Controller_Dispatcher_Standard->getParam('useDefaultControllerAlways') && !empty($controller)) {
					return "";
				}
			
				$className = $Zend_Controller_Dispatcher_Standard->getDefaultControllerClass($request);
			} else {
				$className = $Zend_Controller_Dispatcher_Standard->getControllerClass($request);
				if (!$className) {
					$className = $Zend_Controller_Dispatcher_Standard->getDefaultControllerClass($request);
				}
			}
			return $className;
		}
    
	
		private $resolved = false;
	}
	
	$zf1Plugin = new ZF1Plugin();
	$zf1Plugin->setWatchedFunction("Zend_Controller_Dispatcher_Standard::dispatch", array($zf1Plugin, "resolveMVCEnter"), array($zf1Plugin, "resolveMVCLeave"));