<?php

namespace Core\Packages\shop;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SurveyQuestion
 *
 * @package App\Models\Common
 * @property int    $id
 * @property string $kind
 * @property string $question
 * @property array  $options
 */
class SurveyQuestion extends Model
{
	const KIND_RADIO_BUTTON = 'radio_button';
	const KIND_CHECK_BOX    = 'check_box';
	const KIND_TEXT_BOX     = 'text_box';
	const KIND_NUMBER       = 'number';
	const KIND_IMAGE        = 'image';

	const KINDS = [
		self::KIND_RADIO_BUTTON,
		self::KIND_CHECK_BOX,
		self::KIND_TEXT_BOX,
		self::KIND_NUMBER,
		self::KIND_IMAGE,
	];

	protected $casts = [
		'options' => 'array',
	];
}
