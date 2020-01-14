<?php

class AdminMyModCommentsController extends ModuleAdminController
{
	public function __construct()
	{
		// Set variables
		$this->table = 'mymod_comment';
		$this->className = 'MyModComment';
		$this->fields_list = array(
			'id_mymod_comment' => array('title' => Context::getContext()->getTranslator()->trans('ID'), 'align' => 'center', 'width' => 25),
			'shop_name' => array('title' => Context::getContext()->getTranslator()->trans('Shop'), 'width' => 120, 'filter_key' => 's!name'),
			'firstname' => array('title' => Context::getContext()->getTranslator()->trans('Firstname'), 'width' => 120),
			'lastname' => array('title' => Context::getContext()->getTranslator()->trans('Lastname'), 'width' => 140),
			'email' => array('title' => Context::getContext()->getTranslator()->trans('E-mail'), 'width' => 150),
			'product_name' => array('title' => Context::getContext()->getTranslator()->trans('Product'), 'width' => 100, 'filter_key' => 'pl!name'),
			'grade_display' => array('title' => Context::getContext()->getTranslator()->trans('Grade'), 'align' => 'right', 'width' => 80, 'filter_key' => 'a!grade'),
			'comment' => array('title' => Context::getContext()->getTranslator()->trans('Comment'), 'search' => false),
			'date_add' => array('title' => Context::getContext()->getTranslator()->trans('Date add'), 'type' => 'date'),
		);

		// Set fields form for form view
		$this->context = Context::getContext();
		$this->context->controller = $this;
		$this->fields_form = array(
			'legend' => array('title' => Context::getContext()->getTranslator()->trans('Add / Edit Comment'), 'image' => '../img/admin/contact.gif'),
			'input' => array(
				array('type' => 'text', 'label' => Context::getContext()->getTranslator()->trans('Firstname'), 'name' => 'firstname', 'size' => 30, 'required' => true),
				array('type' => 'text', 'label' => Context::getContext()->getTranslator()->trans('Lastname'), 'name' => 'lastname', 'size' => 30, 'required' => true),
				array('type' => 'text', 'label' => Context::getContext()->getTranslator()->trans('E-mail'), 'name' => 'email', 'size' => 30, 'required' => true),
				array('type' => 'select', 'label' => Context::getContext()->getTranslator()->trans('Product'), 'name' => 'id_product', 'required' => true, 'default_value' => 1, 'options' => array('query' => Product::getProducts($this->context->cookie->id_lang, 1, 1000, 'name', 'ASC'), 'id' => 'id_product', 'name' => 'name')),
				array('type' => 'text', 'label' => Context::getContext()->getTranslator()->trans('Grade'), 'name' => 'grade', 'size' => 30, 'required' => true, 'desc' => Context::getContext()->getTranslator()->trans('Grade must be between 1 and 5')),
				array('type' => 'textarea', 'label' => Context::getContext()->getTranslator()->trans('Comment'), 'name' => 'comment', 'cols' => 50, 'rows' => 5, 'required' => false),
			),
			'submit' => array('title' => Context::getContext()->getTranslator()->trans('Save'))
		);

		// Enable bootstrap
		$this->bootstrap = true;

		// Call of the parent constructor method
		parent::__construct();

		// Update the SQL request of the HelperList
		$this->_select = "s.`name` as shop_name, pl.`name` as product_name, CONCAT(a.`grade`, '/5') as grade_display";
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = a.`id_product` AND pl.`id_lang` = '. (int)$this->context->language->id.' AND pl.`id_shop` = a.`id_shop`)
		LEFT JOIN `'._DB_PREFIX_.'shop` s ON (s.`id_shop` = a.`id_shop`)';

		// Add actions
		$this->addRowAction('view');
		$this->addRowAction('delete');
		$this->addRowAction('edit');

		// Add bulk actions
		$this->bulk_actions = array(
			'delete' => array(
				'text' => Context::getContext()->getTranslator()->trans('Delete selected'),
				'confirm' => Context::getContext()->getTranslator()->trans('Would you like to delete the selected items?'),
			),
			'myaction' => array(
				'text' => Context::getContext()->getTranslator()->trans('My Action'), 'confirm' => Context::getContext()->getTranslator()->trans('Are you sure?'),
			)
		);

		// Define meta and toolbar title
		$this->meta_title = Context::getContext()->getTranslator()->trans('Comments on Product');
		if (Tools::getIsset('viewmymod_comment'))
			$this->meta_title = Context::getContext()->getTranslator()->trans('View comment').' #'. Tools::getValue('id_mymod_comment');
		$this->toolbar_title[] = $this->meta_title;
	}

	protected function processBulkMyAction()
	{
		Tools::dieObject($this->boxes);
	}

	public function renderView()
	{
		// Build delete link
		$admin_delete_link = $this->context->link->getAdminLink('AdminMyModComments').'&deletemymod_comment&id_mymod_comment='.(int)$this->object->id;

		// Build admin product link
		$admin_product_link = $this->context->link->getAdminLink('AdminProducts').'&updateproduct&id_product='.(int)$this->object->id_product.'&key_tab=ModuleMymodcomments';

		// If author is known as a customer, build admin customer link
		$admin_customer_link = '';
		$customers = Customer::getCustomersByEmail($this->object->email);
		if (isset($customers[0]['id_customer']))
			$admin_customer_link = $this->context->link->getAdminLink('AdminCustomers').'&viewcustomer&id_customer='.(int)$customers[0]['id_customer'];

		// Add delete shortcut button to toolbar
		$this->page_header_toolbar_btn['delete'] = array(
			'href' => $admin_delete_link,
			'desc' => Context::getContext()->getTranslator()->trans('Delete it'),
			'icon' => 'process-icon-delete',
			'js' => "return confirm('".Context::getContext()->getTranslator()->trans('Are you sure you want to delete it ?')."');",
		);

		$this->object->loadProductName();
		$tpl = $this->context->smarty->createTemplate(dirname(__FILE__). '/../../views/templates/admin/view.tpl');
		$tpl->assign('mymodcomment', $this->object);
		$tpl->assign('admin_product_link', $admin_product_link);
		$tpl->assign('admin_customer_link', $admin_customer_link);

		return $tpl->fetch();
	}
}