<?php

class CHtml {
    
    /**
     * 
     * Рисует комбо-бокс, по входному массиву, указанного класса
     * и отмечает в нем указаный элемент как выбранный
     * ключи массива задаются в последнем параметре (по умолчанию: id, value)
     * 
     * 
     * @param array $input (0=>array(id=>1,value=>hello),...)
     * @param string $name Имя переменной для формы
     * @param int $selected id элемента который будет помечен как выбранный
     * @param string $classname имя класса для select
     * @param array $keys массив переопределяющий ключи входного массива
     */
    public static function drawCombo_($input, $name, $selected = '', $keys = array(), $default = '-- не указан --', $classname = "form-control") {
        
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
             
        return sprintf('<select name="%s" class="%s">%s</select>', $name, $classname, join(PHP_EOL, $options));
    }
    
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

}
