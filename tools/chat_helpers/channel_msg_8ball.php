<?php declare(strict_types=1);

function shared_channel_msg_8ball() {
	static $answers = array(
		'Signs point to yes.',
		'Yes.',
		'Reply hazy, try again.',
		'Without a doubt.',
		'My sources say no.',
		'As I see it, yes.',
		'You may rely on it.',
		'Concentrate and ask again.',
		'Outlook not so good.',
		'It is decidedly so.',
		'Better not tell you now.',
		'Very doubtful.',
		'Yes - definitely.',
		'It is certain.',
		'Cannot predict now.',
		'Most likely.',
		'Ask again later.',
		'My reply is no.',
		'Outlook good.',
		'Don\'t count on it.'
	);

	return $answers[rand(0, count($answers) - 1)];
}
