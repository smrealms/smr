<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;

class ContactForm extends AccountPage {

	use ReusableTrait;

	public string $file = 'contact.php';

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Contact Form');

		$container = new ContactFormProcessor();
		$template->assign('ProcessingHREF', $container->href());

		$template->assign('From', $account->getLogin());
	}

}
