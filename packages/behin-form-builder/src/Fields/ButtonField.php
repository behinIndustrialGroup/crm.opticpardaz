<?php

namespace MyFormBuilder\Fields;

class ButtonField extends AbstractField
{
    public function render(): string
    {
        $s = '<button id="'. $this->attributes['id'] .'">';
        $s .= trans('fields.' . $this->name);
        $s .= '</button>';
        return $s;
    }
}
