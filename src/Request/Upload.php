<?php

namespace FastDog\Media\Request;

use FastDog\Users\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Загрузка файла
 *
 * @package FastDog\Media\Request
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class Upload extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        \Auth::check();
        if (!\Auth::guest() && \Auth::getUser()->type == User::USER_TYPE_ADMIN) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'parent_id' => 'required',
            'parent_type' => 'required',
        ];
    }


}
