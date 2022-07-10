<?php declare(strict_types=1);

const IRC_8BALL_RESPONSES = [
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
	'Don\'t count on it.',
];

function shared_channel_msg_8ball(): string {
	return array_rand_value(IRC_8BALL_RESPONSES);
}
