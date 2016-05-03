<?php

class CHtml {

    public static function drawCombo($input, $selected = null, $params = array()) {
        
        $keys = get_param($params, 'keys', array());
        $default = get_param($params, 'default', '-- не указан --');
        $htmlOptions = get_param($params, 'htmlOptions', array());
        
        // если в хтмлОпциях нет параметра класса, то добавим дефолтный
        if (get_param($htmlOptions,'class') === false) {
            $htmlOptions['class'] = 'form-control';
        }
        
        $key_id  = get_param($keys, 'id', 'id');
        $key_val = get_param($keys, 'title','title');
        
        $options = array();
        
        if (is_array($input)) {
            if ($default !== null) {
                // первый элемент в списке (по умолчанию) - по сути просто метка
                $options[] = sprintf('<option value="">%s</option>', $default);
            }
            foreach ($input as $item) {
                $value = get_param($item, $key_id, 0);
                $label = get_param($item, $key_val,'?');
                $select = $value == $selected ? 'selected' : '';
                $options[] = sprintf('<option value="%d" %s>%s</option>', $value, $select, $label);
            }
        }
        
        $result = '<select ';
        foreach ($htmlOptions as $key => $value) {
            $result .= sprintf('%s="%s" ', $key, $value);
        }
        $result .= '>';
        $result .= join(PHP_EOL, $options);
        $result .= '</select>';
        
        return $result;
    }
    
    public static function drawLink($text, $param = array()) {
        
        $result = "<a ";
        foreach ($param as $key => $value) {
            $result .= $key;
            $result .= '="' . $value .'" ';
        }
        $result .= ">$text</a>";
        return $result;
    }


	public static function createTag($tagName, $htmlOptions = [], $content = []) {

		if (is_null($htmlOptions)) $htmlOptions = [];
		if (!is_array($content)) $content = array($content);
		if (!is_array($htmlOptions)) $htmlOptions = array($htmlOptions);

		$result = '<' . $tagName;
		foreach ($htmlOptions as $param => $option) {
			$result .= $option !== null ? sprintf(' %s="%s"', $param, $option) : sprintf(' %s', $param);
		}

		if (!count($content)) return $result . '/>' . PHP_EOL; // <input type="text"/>

		$result .= '>' . PHP_EOL;
		$result .= join(PHP_EOL, $content);
		$result .= '</' . $tagName . '>' . PHP_EOL;

		return $result;
	}

	public static function createButton($text, $options = null) {

		$options['class'] = get_param($options, 'class', 'btn btn-default');
		$options['type'] = get_param($options, 'type', 'button');

		return self::createTag('button', $options, $text);
	}

	public static function createLink($text, $href = '#', $options = null) {

		$options['href'] = $href;

		return self::createTag('a', $options, $text);
	}

	public static function createOption($title, $value, $options = null) {
		$options['value'] = $value;

		return self::createTag('option', $options, $title);
	}

	public static function createIcon($icon = '') {

		return self::createTag('i', ['class' => "glyphicon glyphicon-$icon"], ' ');
	}
}
