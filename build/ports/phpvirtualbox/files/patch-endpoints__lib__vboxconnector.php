--- endpoints/lib/vboxconnector.php.orig	2025-10-23 21:28:24.441160000 +0200
+++ endpoints/lib/vboxconnector.php	2025-10-23 22:36:44.000000000 +0200
@@ -113,6 +113,8 @@
 	 */
 	var $dsep = null;
 
+	var $client = null;
+
 	/**
 	 * Obtain configuration settings and set object vars
 	 * @param boolean $useAuthMaster use the authentication master obtained from configuration class
@@ -388,7 +390,8 @@
 
 			// The amount of time we will wait for events is determined by
 			// the amount of listeners - at least half a second
-			$listenerWait = max(100,intval(500/count($this->persistentRequest['vboxEventListeners'])));
+			$listenerCount = count($this->persistentRequest['vboxEventListeners']);
+			$listenerWait = max(100,intval(500/($listenerCount > 0 ? $listenerCount : 1)));
 		}
 
 		// Get events from each configured event listener
@@ -500,6 +503,7 @@
 								'enabled' => $vrde->enabled,
 								'ports' => $vrde->getVRDEProperty('TCP/Ports'),
 								'netAddress' => $vrde->getVRDEProperty('TCP/Address'),
+								'SecurityMethod' => $vrde->getVRDEProperty('Security/Method'),
 								'VNCPassword' => $vrde->getVRDEProperty('VNCPassword'),
 								'authType' => (string)$vrde->authType,
 								'authTimeout' => $vrde->authTimeout
@@ -1875,8 +1879,8 @@
 			$m->Platform->X86->setCPUProperty('LongMode', ($guestOS->is64Bit ? 1 : 0));
 		}
 
-		/* secureBootEnabled reported incorrectly by vboxwebsrv
 		$oldFirmware = (string)$m->FirmwareSettings->firmwareType;
+		/* secureBootEnabled reported incorrectly by vboxwebsrv
 		$oldSecureBoot = ($oldFirmware != 'BIOS' && $m->nonVolatileStore->uefiVariableStore != null ? $m->nonVolatileStore->uefiVariableStore->secureBootEnabled : false);
 		*/
 	
@@ -1891,12 +1895,12 @@
 		$m->Platform->X86->setCPUProperty('LongMode', (strpos($args['OSTypeId'],'_64') > - 1 ? 1 : 0));
 		$m->trustedPlatformModule->type = $args['trustedPlatformModule']['type'];
 
-		/* secureBootEnabled reported incorrectly by vboxwebsrv
 		if($oldFirmware == 'BIOS' && $args['firmwareType'] == 'EFI') {
 			$m->nonVolatileStore->initUefiVariableStore(0);
 			$m->nonVolatileStore->uefiVariableStore->enrollOraclePlatformKey();
 			$m->nonVolatileStore->uefiVariableStore->enrollDefaultMsSignatures();
 		}
+		/* secureBootEnabled reported incorrectly by vboxwebsrv
 		if($args['firmwareType'] != 'BIOS' && $oldSecureBoot != (bool)$args['secureBootEnabled']) {
 			$m->nonVolatileStore->uefiVariableStore->secureBootEnabled = (bool)$args['secureBootEnabled'];
 		}
@@ -1938,11 +1942,13 @@
 			* @remarks This must match GMMR0Init; currently we only support page fusion on
 			 *          all 64-bit hosts except Mac OS X */
 
+			/* Page Fusion does not work properly in VirtualBox 7.2
+			 * returns "Page fusion is only supported on 64-bit hosts"
 			if($this->vbox->host->getProcessorFeature('LongMode')) {
 
 				$m->pageFusionEnabled = $args['pageFusionEnabled'];
 			}
-
+			*/
 			$m->Platform->X86->HPETEnabled = $args['HPETEnabled'];
 			$m->setExtraData("VBoxInternal/Devices/VMMDev/0/Config/GetHostTimeDisabled", $args['disableHostTimeSync']);
 			$m->keyboardHIDType = $args['keyboardHIDType'];
@@ -1970,6 +1976,7 @@
 				$m->VRDEServer->setVRDEProperty('TCP/Ports',$args['VRDEServer']['ports']);
 				if(@$this->settings->enableAdvancedConfig)
 					$m->VRDEServer->setVRDEProperty('TCP/Address',$args['VRDEServer']['netAddress']);
+				$m->VRDEServer->setVRDEProperty('Security/Method',$args['VRDEServer']['SecurityMethod']);
 				$m->VRDEServer->setVRDEProperty('VNCPassword',$args['VRDEServer']['VNCPassword'] ? $args['VRDEServer']['VNCPassword'] : null);
 				$m->VRDEServer->authType = ($args['VRDEServer']['authType'] ? $args['VRDEServer']['authType'] : 'Null');
 				$m->VRDEServer->authTimeout = $args['VRDEServer']['authTimeout'];
@@ -3713,6 +3720,7 @@
 					'enabled' => $vrde->enabled,
 					'ports' => $vrde->getVRDEProperty('TCP/Ports'),
 					'netAddress' => $vrde->getVRDEProperty('TCP/Address'),
+					'SecurityMethod' => $vrde->getVRDEProperty('Security/Method'),
 					'VNCPassword' => $vrde->getVRDEProperty('VNCPassword'),
 					'authType' => (string)$vrde->authType,
 					'authTimeout' => $vrde->authTimeout,
@@ -3859,6 +3867,9 @@
 
 		// Save and register
 		$m->saveSettings();
+		if((string)$m->FirmwareSettings->firmwareType == 'EFI') {
+			$m->nonVolatileStore->initUefiVariableStore(0);
+		}
 		$this->vbox->registerMachine($m->handle);
 		$vm = $m->id;
 		$m->releaseRemote();
@@ -4291,11 +4302,13 @@
 			'snapshotFolder' => $m->snapshotFolder,
 			'ClipboardMode' => (string)$m->ClipboardMode,
 			'monitorCount' => $m->GraphicsAdapter->monitorCount,
-			'pageFusionEnabled' => $m->pageFusionEnabled,
+			// Page Fusion does not work properly in 7.2
+			//'pageFusionEnabled' => $m->pageFusionEnabled,
 			'VRDEServer' => (!$m->VRDEServer ? null : array(
 				'enabled' => $m->VRDEServer->enabled,
 				'ports' => $m->VRDEServer->getVRDEProperty('TCP/Ports'),
 				'netAddress' => $m->VRDEServer->getVRDEProperty('TCP/Address'),
+				'SecurityMethod' => $m->VRDEServer->getVRDEProperty('Security/Method'),
 				'VNCPassword' => $m->VRDEServer->getVRDEProperty('VNCPassword'),
 				'authType' => (string)$m->VRDEServer->authType,
 				'authTimeout' => $m->VRDEServer->authTimeout,
