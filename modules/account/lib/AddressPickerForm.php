<?php

use Openbizx\Openbizx;
use Openbizx\Easy\PickerForm;

class AddressPickerForm extends PickerForm
{
	public function fetchData()
	{
		$result = parent::fetchData();
		$prtFormName = Openbizx::getObject($this->parentFormName)->parentFormName;
		if( $prtFormName=='account.form.AccountEditShippingAddressForm' || 
			$prtFormName=='account.form.AccountEditBillingAddressForm'
			){
			$account_id = Openbizx::getObject(Openbizx::getObject($this->parentFormName)->parentFormName)->recordId;
			$result['account_id'] = $account_id;	
			$productRec = Openbizx::getObject("account.do.AccountPickDO")->fetchById($account_id);			
			$result['account_name'] = $productRec['name'];
		}
		return $result;
	}
		
}
