<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\Player;

class PersonalBankRenderer {

public static function render(PersonalBankProcessor $ProcessingPage, Player $ThisPlayer): void {
?>
Hello <?php echo $ThisPlayer->getDisplayName(); ?>
<br /><br />

Balance: <b><?php echo number_format($ThisPlayer->getBank()); ?></b><?php
if ($ThisPlayer->getBank() >= MAX_MONEY) { ?>
	(Account is Full)<?php
} ?>
<br /><br />
<h2>Make transaction</h2>
<br />

<form method="POST" action="<?php echo $ProcessingPage->href(); ?>">
	Amount:&nbsp;<input type="number" name="amount" min="1" required size="10"><br /><br />
	<?php echo $ProcessingPage->actionDeposit->html(); ?>
	&nbsp;&nbsp;
	<?php echo $ProcessingPage->actionWithdraw->html(); ?>
</form>

<?php
}

}
