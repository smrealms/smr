<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Pages\Shared\Admin\Unigen\GalaxyDetailsRenderer;

class CreateGalaxiesRenderer {

	/**
	 * @param array{value: string, href: string} $Submit
	 * @param array<int, array{Name: string, Width: int, Height: int, Type: string, ForceMaxHours: float}> $Galaxies
	 */
	public static function render(
		bool $GameEnabled,
		string $UpdateNumGalsHREF,
		array $Submit,
		string $GenerateHREF,
		string $UploadSmrFileHREF,
		int $NumGals,
		array $Galaxies,
	): void {
		?>
		<form method="POST" action="<?php echo $UpdateNumGalsHREF; ?>">
			Number of Galaxies:
			<input class="center" type="number" min="1" max="30" name="num_gals" value="<?php echo $NumGals; ?>" />
			<?php echo create_submit_display('Update'); ?>
		</form>
		<br />
		<?php GalaxyDetailsRenderer::render(Galaxies: $Galaxies, GameEnabled: $GameEnabled, Submit: $Submit); ?>

		<br /><br />
		<form method="POST" enctype="multipart/form-data" action="<?php echo $UploadSmrFileHREF; ?>">
			Or generate the universe from a SMR file:<br />
			<input type="file" name="smr_file" />&nbsp;
			<?php echo create_submit_display('Upload SMR File'); ?>
		</form>

		<br /><br />
		Or automatically generate a pre-populated map: <a href="<?php echo $GenerateHREF; ?>" class="submitStyle">Generate</a>
		<br />
		<small><span class="bold">WARNING: </span> This is slow!</small>

		<?php
	}

}
