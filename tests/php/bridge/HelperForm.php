<?php

class HelperForm
{
    public $show_toolbar;
    public $table;
    public $default_form_language;
    public $allow_employee_form_lang;
    public $module;
    public $identifier;
    public $submit_action;
    public $currentIndex;
    public $token;
    public $tpl_vars;
    public $override_folder;

    public function generateForm($forms)
    {
        $html = '';
        foreach ($forms as $form) {
            if (!isset($form['form']['input'])) {
                continue;
            }
            foreach ($form['form']['input'] as $input) {
                if (isset($input['name'])) {
                    $html .= 'name="' . $input['name'] . '" ';
                }
            }
            if (isset($form['form']['submit']['title'])) {
                $html .= 'submit_action="' . $this->submit_action . '"';
            }
        }

        return $html;
    }
}
