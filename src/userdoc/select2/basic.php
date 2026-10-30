<?php // Basic usage of Select2 widget: tags typed in freely (Select2 4 binds to a select)
$this->widget(
    'booster.widgets.TbSelect2',
    array(
        'name' => 'clevertech',
        'data' => array('clever' => 'clever', 'is' => 'is', 'better' => 'better', 'clevertech' => 'clevertech'),
        'htmlOptions' => array('multiple' => 'multiple'),
        'options' => array(
            'tags' => true,
            'placeholder' => 'type clever, or is, or just type!',
            'width' => '40%',
            'tokenSeparators' => array(',', ' ')
        )
    )
);
