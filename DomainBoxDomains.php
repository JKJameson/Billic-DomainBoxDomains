<?php
class DomainBoxDomains {
	public $settings = array(
		'orderform_vars' => array(
			'domain',
			'extension',
			'action',
			'transfer_code'
		) ,
		'description' => 'Automate the creation of domains through DomainBox.',
	);
	function user_cp($array) {
		global $billic, $db;
		$service = $array['service'];
		if (!empty($_GET['Action'])) {
			switch ($_GET['Action']) {
				case 'NameServers':
					$r = $this->soap('QueryDomainNameservers', array(
						'DomainName' => $service['domain'],
					));
					if (isset($_POST['update'])) {
						$nameservers = array();
						for ($i = 1;$i <= 5;$i++) {
							if (empty($_POST['ns' . $i])) {
								break;
							}
							$nameservers['NS' . $i] = $_POST['ns' . $i];
						}
						$r = $this->soap('ModifyDomainNameservers', array(
							'DomainName' => $service['domain'],
							'Nameservers' => $nameservers,
						));
						if ($r->ModifyDomainNameserversResult->ResultCode == 100) {
							echo '<div class="alert alert-success" role="alert">Name Servers successfully updated!</div>';
							return;
						} else {
							err('Failed to update: ' . $r->ModifyDomainNameserversResult->ResultMsg);
						}
						exit;
					}
					$billic->show_errors();
					echo '<form method="POST"><table class="table table-striped"><tr><th>#</th><th>Nameserver</th></tr>';
					$total = 5;
					for ($i = 1;$i <= $total;$i++) {
						$ns = 'NS' . $i;
						echo '<tr><td>' . $i . '</td><td><input type="text" class="form-control" name="ns' . $i . '" value="' . safe($r->QueryDomainNameserversResult->Nameservers->$ns) . '"></td></tr>';
					}
					echo '<tr><td colspan="2" align="center"><input type="submit" class="btn btn-success" name="update" value="Update &raquo;" onClick="javascript:this.value=\'Updating. Please wait...\';this.readOnly=true"></table></form>';
					exit;
				break;
				case 'ChangeContactDetails':
					$r = $this->soap('QueryDomainContacts', array(
						'DomainName' => $service['domain'],
					));
					if (isset($_POST['update'])) {
						$contact = array(
							'Name' => $_POST['name'],
							'Organisation' => $_POST['company'],
							'Street1' => $_POST['address-line-1'],
							'Street2' => $_POST['address-line-2'],
							'Street3' => $_POST['address-line-3'],
							'City' => $_POST['city'],
							'State' => $_POST['state'],
							'Postcode' => $_POST['zipcode'],
							'CountryCode' => $_POST['country'],
							'Telephone' => '+' . $_POST['phone-cc'] . '.' . $_POST['phone'],
							'Email' => $_POST['email'],
						);
						$r = $this->soap('ModifyDomainContacts', array(
							'DomainName' => $service['domain'],
							'Contacts' => array(
								'Registrant' => $contact,
								//'Admin' => $contact,
								//'Billing' => $contact,
								//'Tech' => $contact,
								
							) ,
							'AcceptTerms' => true,
						));
						if ($r->ModifyDomainContactsResult->ResultCode == 100) {
							echo '<div class="alert alert-success" role="alert">Contacts successfully updated!</div>';
							return;
						} else {
							err('Failed to update: ' . $r->ModifyDomainContactsResult->ResultMsg);
						}
					}
					$billic->show_errors();
					echo '<form method="POST"><table class="table table-striped">';
					echo '<tr><td>Name:</td><td><input type="text" class="form-control" name="name" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->Name) . '"></td></tr>';
					echo '<tr><td>Company:</td><td><input type="text" class="form-control" name="company" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->Organisation) . '"></td></tr>';
					echo '<tr><td>Email:</td><td><input type="text" class="form-control" name="email" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->Email) . '"></td></tr>';
					echo '<tr><td>Address Line 1:</td><td><input type="text" class="form-control" name="address-line-1" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->Street1) . '"></td></tr>';
					echo '<tr><td>Address Line 2:</td><td><input type="text" class="form-control" name="address-line-2" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->Street2) . '"></td></tr>';
					echo '<tr><td>Address Line 3:</td><td><input type="text" class="form-control" name="address-line-3" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->Street3) . '"></td></tr>';
					echo '<tr><td>City:</td><td><input type="text" class="form-control" name="city" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->City) . '"></td></tr>';
					echo '<tr><td>State:</td><td><input type="text" class="form-control" name="state" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->State) . '"></td></tr>';
					echo '<tr><td>Zip Code:</td><td><input type="text" class="form-control" name="zipcode" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->Postcode) . '"></td></tr>';
					echo '<tr><td>Country:</td><td><input type="text" class="form-control" name="country" value="' . safe($r->QueryDomainContactsResult->Contacts->Registrant->CountryCode) . '"></td></tr>';
					$tel = explode('.', str_replace('+', '', $r->QueryDomainContactsResult->Contacts->Registrant->Telephone));
					echo '<tr><td>Phone:</td><td><div class="form-group"><div class="col-sm-1"><input type="text" class="form-control" value="+" readonly></div><div class="col-sm-1"><input type="text" class="form-control" name="phone-cc" value="' . safe($tel[0]) . '" maxlength="3"></div><div class="col-sm-9"><input type="text" class="form-control" name="phone" value="' . safe($tel[1]) . '"></div></div></td></tr>';
					echo '<tr><td colspan="2" align="center"><input type="submit" class="btn btn-success" name="update" value="Update &raquo;" onClick="javascript:this.value=\'Updating. Please wait...\';this.readOnly=true"></table></form>';
					exit;
				break;
				case 'PrivacyProtection':
					if (isset($_POST['action'])) {
						if ($_POST['action'] == 'Disable') {
							$r = $this->soap('ModifyDomainPrivacy', array(
								'DomainName' => $service['domain'],
								'ApplyPrivacy' => false,
							));
						} else if ($_POST['action'] == 'Enable') {
							$r = $this->soap('ModifyDomainPrivacy', array(
								'DomainName' => $service['domain'],
								'ApplyPrivacy' => true,
							));
						} else {
							err('Invalid Action');
						}
						if ($r->ModifyDomainPrivacyResult->ResultCode == 100) {
							echo '<div class="alert alert-success" role="alert">Privacy successfully updated!</div>';
							return;
						} else {
							err('Failed to update: ' . $r->ModifyDomainPrivacyResult->ResultMsg);
						}
						exit;
					}
					$r = $this->soap('QueryDomainPrivacy', array(
						'DomainName' => $service['domain'],
					));
					$billic->show_errors();
					echo '<form method="POST">';
					if ($r->QueryDomainPrivacyResult->ApplyPrivacy === true) {
						echo 'Privacy Protection is Enabled. <input type="submit" class="btn btn-danger" name="action" value="Disable">';
					} else if ($r->QueryDomainPrivacyResult->ApplyPrivacy === false) {
						echo 'Privacy Protection is Disabled. <input type="submit" class="btn btn-success" name="action" value="Enable">';
					} else {
						echo 'Unable to get Privacy Protection status at this time.';
					}
					echo '</form>';
					exit;
					break;
				case 'TheftProtection':
					if (isset($_POST['action'])) {
						if ($_POST['action'] == 'Disable') {
							$r = $this->soap('ModifyDomainLock', array(
								'DomainName' => $service['domain'],
								'ApplyLock' => false,
							));
						} else if ($_POST['action'] == 'Enable') {
							$r = $this->soap('ModifyDomainLock', array(
								'DomainName' => $service['domain'],
								'ApplyLock' => true,
							));
						} else {
							err('Invalid Action');
						}
						if ($r->ModifyDomainLockResult->ResultCode == 100) {
							echo '<div class="alert alert-success" role="alert">Lock successfully updated!</div>';
							return;
						} else {
							err('Failed to update: ' . $r->ModifyDomainLockResult->ResultMsg);
						}
						exit;
					}
					$billic->show_errors();
					echo '<form method="POST">';
					$r = $this->soap('QueryDomainLock', array(
						'DomainName' => $service['domain'],
					));
					if ($r->QueryDomainLockResult->ApplyLock === true) {
						echo 'Transfer Lock is Enabled. <input type="submit" class="btn btn-danger" name="action" value="Disable">';
					} else if ($r->QueryDomainLockResult->ApplyLock === false) {
						echo 'Transfer Lock is Disabled. <input type="submit" class="btn btn-success" name="action" value="Enable">';
					} else {
						echo 'Unable to get Transfer Lock status at this time.';
					}
					echo '</form>';
					exit;
					break;
				case 'TransferAuthCode':
					if (isset($_POST['generate'])) {
						$r = $this->soap('ModifyDomainAuthcode', array(
							'DomainName' => $service['domain'],
							'GenerateNew' => true,
						));
						if ($r->ModifyDomainAuthcodeResult->ResultCode == 100) {
							echo '<div class="alert alert-success" role="alert">our Transfer Auth Code has been set to: <kbd>' . $r->ModifyDomainAuthcodeResult->AuthCode . '</kbd></div>';
							return;
						} else {
							err('Failed to update: ' . $r->ModifyDomainAuthcodeResult->ResultMsg);
						}
						exit;
					}
					$billic->show_errors();
					echo '<form method="POST">';
					echo '<input type="submit" class="btn btn-success" name="generate" value="Generate a new Transfer Auth Code for this domain">';
					echo '</form>';
					exit;
					break;
				case 'ChildNameServers':
					$dom_url = 'api/domains/details-by-name.json?domain-name=' . urlencode($service['domain']) . '&options=All';
					$dom = $this->curl($dom_url);
					if (isset($_POST['update'])) {
						if (!empty($_POST['new_cns']) && !empty($_POST['new_ip'])) {
							$post = 'order-id=' . $dom['orderid'] . '&cns=' . urlencode($_POST['new_cns'] . '.' . $service['domain']);
							foreach ($dom['cns'] as $cns => $ips) {
								if ($cns == $_POST['new_cns'] . '.' . $service['domain']) {
									foreach ($ips as $ip) {
										if (empty($ip)) {
											continue;
										}
										$post.= '&ip=' . urlencode($ip);
									}
								}
							}
							$post.= '&ip=' . urlencode($_POST['new_ip']);
							$add = $this->curl('api/domains/add-cns.json', $post);
							if (!array_key_exists('status', $add)) {
								echo '<b><font color="green">Successfully Updated!</font></b>';
							} else {
								err('API Error: ' . $add['status']);
							}
						}
						foreach ($_POST['cns'] as $cns => $ips) {
							foreach ($ips as $ip) {
								//if (!in_array($ip, $dom['cns'][$cns]
								//https://httpapi.com/api/domains/modify-cns-ip.json?auth-userid=0&api-key=key&order-id=0&cns=ns1.domain.com&old-ip=0.0.0.0&new-ip=1.1.1.1
								if (empty($ip)) {
									continue;
								}
								$post.= '&ip=' . urlencode($ip);
							}
						}
						$dom = $this->curl($dom_url);
					}
					$billic->show_errors();
					echo '<form method="POST"><table class="table table-striped"><tr><tr><th>Name Servers</th><th>IP Address</th></tr>';
					foreach ($dom['cns'] as $cns => $ips) {
						foreach ($ips as $ip) {
							echo '<tr><td>' . safe($cns) . '</td><td><input type="text" class="form-control" name="cns[' . $cns . '][]" value="' . safe($ip) . '" style="width: 250px"></td></tr>';
						}
					}
					echo '<tr><td><input type="text" class="form-control" name="new_cns" value="" style="width: 50px">.' . $service['domain'] . '</td><td><input type="text" class="form-control" name="new_ip" value="" style="width: 250px"></td></tr>';
					echo '<tr><td colspan="2" align="center"><input type="submit" class="btn btn-default" name="update" value="Update &raquo;" onClick="javascript:this.value=\'Updating. Please wait...\';this.readOnly=true"></table></form>';
					exit;
					break;
				}
			}
			echo '<ul class="nav nav-pills">';
			echo '<li role="presentation" class="active"><a href="' . $billic->uri() . 'Action/NameServers/"><i class="icon-globe-world"></i> Name Servers</a></li>';
			echo '<li role="presentation" class="active"><a href="' . $billic->uri() . 'Action/ChangeContactDetails/"><i class="icon-user"></i>  Contact Details</a></li>';
			echo '<li role="presentation" class="active"><a href="' . $billic->uri() . 'Action/PrivacyProtection/"><i class="icon-clipboard"></i> Privacy Protection</a></li>';
			echo '<li role="presentation" class="active"><a href="' . $billic->uri() . 'Action/TheftProtection/"><i class="icon-shield"></i> Theft Protection</a></li>';
			echo '<li role="presentation" class="active"><a href="' . $billic->uri() . 'Action/TransferAuthCode/"><i class="icon-refresh"></i> Get Transfer Auth Code</a></li>';
			// <a href="'.$billic->uri().'Action/ChildNameServers/"><img src="/i/icons/world_edit.png" class="inline16">Register a Child Name Server</a><br><br>
			echo '</ul>';
		}
		function suspend($array) {
			global $billic, $db;
			$service = $array['service'];
			return true;
		}
		function unsuspend($array) {
			global $billic, $db;
			$service = $array['service'];
			return true;
		}
		function terminate($array) {
			global $billic, $db;
			$service = $array['service'];
			return true;
		}
		function create($array) {
			global $billic, $db;
			$contactRegistrant = array(
				'Name' => $array['user']['firstname'] . ' ' . $array['user']['lastname'],
				'Organisation' => $array['user']['companyname'],
				'Street1' => $array['user']['address1'],
				'Street2' => $array['user']['address2'],
				'City' => $array['user']['city'],
				'State' => $array['user']['state'],
				'Postcode' => $array['user']['postcode'],
				'CountryCode' => $array['user']['country'],
				'Telephone' => '+1.' . $array['user']['phonenumber'],
				'Email' => $array['user']['email'],
			);
			$contactAdmin = array(
				'Name' => $array['user']['firstname'] . ' ' . $array['user']['lastname'],
				'Organisation' => $array['user']['companyname'],
				'Street1' => $array['user']['address1'],
				'Street2' => $array['user']['address2'],
				'City' => $array['user']['city'],
				'State' => $array['user']['state'],
				'Postcode' => $array['user']['postcode'],
				'CountryCode' => $array['user']['country'],
				'Telephone' => '+1.' . $array['user']['phonenumber'],
				'Email' => $array['user']['email'],
			);
			$contactBilling = array(
				'Name' => $array['user']['firstname'] . ' ' . $array['user']['lastname'],
				'Organisation' => $array['user']['companyname'],
				'Street1' => $array['user']['address1'],
				'Street2' => $array['user']['address2'],
				'City' => $array['user']['city'],
				'State' => $array['user']['state'],
				'Postcode' => $array['user']['postcode'],
				'CountryCode' => $array['user']['country'],
				'Telephone' => '+1.' . $array['user']['phonenumber'],
				'Email' => $array['user']['email'],
			);
			$contactTech = array(
				'Name' => $array['user']['firstname'] . ' ' . $array['user']['lastname'],
				'Organisation' => $array['user']['companyname'],
				'Street1' => $array['user']['address1'],
				'Street2' => $array['user']['address2'],
				'City' => $array['user']['city'],
				'State' => $array['user']['state'],
				'Postcode' => $array['user']['postcode'],
				'CountryCode' => $array['user']['country'],
				'Telephone' => '+1.' . $array['user']['phonenumber'],
				'Email' => $array['user']['email'],
			);
			$send = array(
				'LaunchPhase' => 'GA',
				'DomainName' => $array['service']['domain'],
				'Period' => 1,
				'ApplyLock' => false,
				'AutoRenew' => false,
				'AutoRenewDays' => 7,
				'ApplyPrivacy' => true,
				'AcceptTerms' => true,
				'Nameservers' => array(
					'NS1' => get_config('DomainBoxDomains_default_ns1') ,
					'NS2' => get_config('DomainBoxDomains_default_ns2') ,
					'NS3' => get_config('DomainBoxDomains_default_ns3') ,
				) ,
				'Contacts' => array(
					'Registrant' => $contactRegistrant,
					'Admin' => $contactAdmin,
					'Billing' => $contactBilling,
					'Tech' => $contactTech,
				) ,
			);
			$r = $this->soap('RegisterDomain', $send);
			if ($r->RegisterDomainResult->ResultCode == 100) {
				return true;
			} else {
				return $r->RegisterDomainResult->ResultMsg;
			}
		}
		function ordercheck($array) {
			global $billic, $db;
			$vars = $array['vars'];
			if (!ctype_alnum(str_replace('-', '', $vars['domain']))) {
				$billic->error('Invalid Domain. Please enter the domain without any dots or extension.', 'domain');
				return;
			}
			$domain = $vars['domain'] . '.' . $vars['extension'];
			if (empty($billic->errors)) {
				$r = $this->soap('CheckDomainAvailability', array(
					'DomainName' => $domain,
					'LaunchPhase' => 'GA',
				));
				if (!$r) {
					$billic->error('The domain checker is currently unavailable. Please try again later. If the problem persists, please contact support.', 'domain');
					return;
				}
				if ($r->CheckDomainAvailabilityResult->ResultCode != 100) {
					$billic->error('API Error: ' . $r->CheckDomainAvailabilityResult->RResultMsg, 'domain');
					return;
				}
			}
			if ($vars['action'] == 'Register') {
				if ($r->CheckDomainAvailabilityResult->AvailabilityStatus != 0) {
					$billic->error($domain . ' is not available. (' . $r->CheckDomainAvailabilityResult->AvailabilityStatusDescr . ')', 'domain');
					return;
				}
			}
			if ($vars['action'] == 'Transfer') {
				if (empty($vars['transfer_code'])) {
					$billic->error('Transfer code is required if transferring a domain.', 'transfer_code');
					return;
				} else if ($r->CheckDomainAvailabilityResult->AvailabilityStatus == 1) {
					$billic->error($domain . ' is not available for transfer. (' . $r->CheckDomainAvailabilityResult->AvailabilityStatusDescr . ')', 'domain');
					return;
				}
			}
			return $domain; // return the domain for the service to be called
			
		}
		private $client;
		function soap($command, $params) {
			if ($this->client === null) {
				$this->client = new SoapClient('https://live.domainbox.net/?wsdl', array(
					'soap_version' => SOAP_1_2
				)); // , 'trace' => 1
				
			}
			$data = call_user_func_array(array(
				$this->client,
				$command
			) , array(
				array(
					'AuthenticationParameters' => array(
						'Reseller' => get_config('DomainBoxDomains_reseller') ,
						'Username' => get_config('DomainBoxDomains_username') ,
						'Password' => get_config('DomainBoxDomains_password') ,
					) ,
					'CommandParameters' => $params,
				)
			));
			/*
			echo "====== REQUEST HEADERS =====" . PHP_EOL;
			 var_dump($this->client->__getLastRequestHeaders());
			 echo "========= REQUEST ==========" . PHP_EOL;
			 var_dump($this->client->__getLastRequest());
			 echo "========= RESPONSE =========" . PHP_EOL;
			 var_dump($this->response);
			*/
			return $data;
		}
		function settings($array) {
			global $billic, $db;
			if (empty($_POST['update'])) {
				echo '<form method="POST"><input type="hidden" name="billic_ajax_module" value="DomainBoxDomains"><table class="table table-striped">';
				echo '<tr><th>Setting</th><th>Value</th></tr>';
				echo '<tr><td>Reseller</td><td><input type="text" class="form-control" name="DomainBoxDomains_reseller" value="' . safe(get_config('DomainBoxDomains_reseller')) . '"></td></tr>';
				echo '<tr><td>Username</td><td><input type="text" class="form-control" name="DomainBoxDomains_username" value="' . safe(get_config('DomainBoxDomains_username')) . '"></td></tr>';
				echo '<tr><td>Password</td><td><input type="password" class="form-control" name="DomainBoxDomains_password" value="' . safe(get_config('DomainBoxDomains_password')) . '"></td></tr>';
				echo '<tr><td>Default NS1</td><td><input type="text" class="form-control" name="DomainBoxDomains_default_ns1" value="' . safe(get_config('DomainBoxDomains_default_ns1')) . '"></td></tr>';
				echo '<tr><td>Default NS2</td><td><input type="text" class="form-control" name="DomainBoxDomains_default_ns2" value="' . safe(get_config('DomainBoxDomains_default_ns2')) . '"></td></tr>';
				echo '<tr><td>Default NS3</td><td><input type="text" class="form-control" name="DomainBoxDomains_default_ns3" value="' . safe(get_config('DomainBoxDomains_default_ns3')) . '"></td></tr>';
				echo '<tr><td colspan="2" align="center"><input type="submit" class="btn btn-default" name="update" value="Update &raquo;"></td></tr>';
				echo '</table></form>';
			} else {
				if (empty($billic->errors)) {
					set_config('DomainBoxDomains_reseller', $_POST['DomainBoxDomains_reseller']);
					set_config('DomainBoxDomains_username', $_POST['DomainBoxDomains_username']);
					set_config('DomainBoxDomains_password', $_POST['DomainBoxDomains_password']);
					set_config('DomainBoxDomains_default_ns1', $_POST['DomainBoxDomains_default_ns1']);
					set_config('DomainBoxDomains_default_ns2', $_POST['DomainBoxDomains_default_ns2']);
					set_config('DomainBoxDomains_default_ns3', $_POST['DomainBoxDomains_default_ns3']);
					$billic->status = 'updated';
				}
			}
		}
	}
	
