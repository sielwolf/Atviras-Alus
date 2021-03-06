<?

class Form_Attributes extends Zend_Form {

	public function init() {
		$db = Zend_Registry::get('db');

		$this->setAction('/brewer/profile');
		$this->setMethod('post');
		$this->addElement('select', 'user_location');
		$this->getElement('user_location')
				->setLabel('Vietovė:');
		$select = $db->select()
				->from("towns")
				->order('town_name ASC');
		$towns = $db->fetchAll($select);
		$this->getElement('user_location')->addMultiOption("", "");
		for ($i = 0; $i < count($towns); $i++) {
			$this->getElement('user_location')->addMultiOption($towns[$i]['town_name'], $towns[$i]['town_name']);
		}
		$this->addElement('checkbox', 'use_other_location');
		$this->getElement('use_other_location')
				->setLabel('Kita:');


		$this->addElement('text', 'user_other_location');
		$this->getElement('user_other_location')
				->addValidator(new Zend_Validate_Alnum());

		$this->addElement('textarea', 'user_about');
		$this->getElement('user_about')
				->setAttrib('COLS', '80')
				->setAttrib('ROWS', '4')
				->setLabel('Apie mane:');
		$this->addElement('checkbox', 'user_mail_comments');
		$this->getElement('user_mail_comments')
				->setLabel('Siųsti pranešimą elektroniniu paštu, kai komentuojamas mano receptas');
		$this->addElement('checkbox', 'beta_tester');
		$this->getElement('beta_tester')
				->setLabel('Tituliniame puslapyje vietoj informacinių blokų rodyti naujienų srautą');
		$this->addElement('checkbox', 'plato');
		$this->getElement('plato')
				->setLabel('Receptuose tankį rodyti Plato sistemos vienetais');
		$this->addElement('submit', 'attributes_action', array('type' => 'submit', 'class' => 'ui-button'));
		$this->getElement('attributes_action')
				->setLabel('išsaugoti paskyros nustatymus')
				->setIgnore(true);
		$this->addElement('hidden', 'action', array('value' => 'attributes'));
	}

	public function loadDefaultDecorators() {
		foreach ($this->getElements() as $element) {
			$element->removeDecorator('DtDdWrapper');
			$element->removeDecorator('Label');
			$element->removeDecorator('HtmlTag');
			$element->removeDecorator('Errors');
		}

		$this->setDecorators(array(array('ViewScript', array('viewScript' => 'brewer/forms/attributes.phtml'))));
	}

}

?>
