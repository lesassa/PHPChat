<?php
return [
	//個人設定
    'inputContainer' => '{{content}}',
    'label' => '',
	'checkboxWrapper' => '{{label}}',
	'submitContainer' => '{{content}}',

	//既存
	'button' => '<button{{attrs}}>{{text}}</button>',
	'checkbox' => '<input type="checkbox" name="{{name}}" value="{{value}}"{{attrs}}>',
	'checkboxFormGroup' => '{{label}}',
	'dateWidget' => '{{year}}{{month}}{{day}}{{hour}}{{minute}}{{second}}{{meridian}}',
	'error' => '<div class="error-message">{{content}}</div>',
	'errorList' => '<ul>{{content}}</ul>',
	'errorItem' => '<li>{{text}}</li>',
	'file' => '<input type="file" name="{{name}}"{{attrs}}>',
	'fieldset' => '<fieldset{{attrs}}>{{content}}</fieldset>',
	'formStart' => '<form{{attrs}}>',
	'formEnd' => '</form>',
	'formGroup' => '{{label}}{{input}}',
	'hiddenBlock' => '<div style="display:none;">{{content}}</div>',
	'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}/>',
	'inputSubmit' => '<input type="{{type}}"{{attrs}}/>',
	'inputContainerError' => '<div class="input {{type}}{{required}} error">{{content}}{{error}}</div>',
	'nestingLabel' => '{{hidden}}<label{{attrs}}>{{input}}{{text}}</label>',
	'legend' => '<legend>{{text}}</legend>',
	'multicheckboxTitle' => '<legend>{{text}}</legend>',
	'multicheckboxWrapper' => '<fieldset{{attrs}}>{{content}}</fieldset>',
	'option' => '<option value="{{value}}"{{attrs}}>{{text}}</option>',
	'optgroup' => '<optgroup label="{{label}}"{{attrs}}>{{content}}</optgroup>',
	'select' => '<select name="{{name}}"{{attrs}}>{{content}}</select>',
	'selectMultiple' => '<select name="{{name}}[]" multiple="multiple"{{attrs}}>{{content}}</select>',
	'radio' => '<input type="radio" name="{{name}}" value="{{value}}"{{attrs}}>',
	'radioWrapper' => '{{label}}',
	'textarea' => '<textarea name="{{name}}"{{attrs}}>{{value}}</textarea>',

];