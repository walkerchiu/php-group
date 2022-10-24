<?php

namespace WalkerChiu\Group\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class GroupFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (string) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'host_type'      => trans('php-group::system.host_type'),
            'host_id'        => trans('php-group::system.host_id'),
            'user_id'        => trans('php-group::system.user_id'),
            'serial'         => trans('php-group::system.serial'),
            'identifier'     => trans('php-group::system.identifier'),
            'script_head'    => trans('php-group::system.script_head'),
            'script_footer'  => trans('php-group::system.script_footer'),
            'options'        => trans('php-group::system.options'),
            'order'          => trans('php-group::system.order'),
            'is_highlighted' => trans('php-group::system.is_highlighted'),
            'is_enabled'     => trans('php-group::system.is_enabled'),

            'name'           => trans('php-group::system.name'),
            'description'    => trans('php-group::system.description'),
            'keywords'       => trans('php-group::system.keywords'),
            'remarks'        => trans('php-group::system.remarks')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'host_type'      => 'required_with:host_id|string',
            'host_id'        => 'required_with:host_type|string',
            'user_id'        => ['nullable','string','exists:'.config('wk-core.table.user').',id'],
            'serial'         => '',
            'identifier'     => 'required|string|max:255',
            'script_head'    => '',
            'script_footer'  => '',
            'options'        => 'nullable|json',
            'order'          => 'nullable|numeric|min:0',
            'is_highlighted' => 'required|boolean',
            'is_enabled'     => 'required|boolean',

            'name'           => 'required|string|max:255',
            'description'    => '',
            'keywords'       => '',
            'remarks'        => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','string','exists:'.config('wk-core.table.group.groups').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.string'               => trans('php-core::validation.string'),
            'id.exists'               => trans('php-core::validation.exists'),
            'host_type.required_with' => trans('php-core::validation.required_with'),
            'host_type.string'        => trans('php-core::validation.string'),
            'host_id.required_with'   => trans('php-core::validation.required_with'),
            'host_id.string'          => trans('php-core::validation.string'),
            'user_id.string'          => trans('php-core::validation.string'),
            'user_id.exists'          => trans('php-core::validation.exists'),
            'identifier.required'     => trans('php-core::validation.required'),
            'identifier.string'       => trans('php-core::validation.string'),
            'identifier.max'          => trans('php-core::validation.max'),
            'options.json'            => trans('php-core::validation.json'),
            'order.numeric'           => trans('php-core::validation.numeric'),
            'order.min'               => trans('php-core::validation.min'),
            'is_highlighted.required' => trans('php-core::validation.required'),
            'is_highlighted.boolean'  => trans('php-core::validation.boolean'),
            'is_enabled.required'     => trans('php-core::validation.required'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'name.required'           => trans('php-core::validation.required'),
            'name.string'             => trans('php-core::validation.string'),
            'name.max'                => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (
                isset($data['host_type'])
                && isset($data['host_id'])
            ) {
                if (
                    config('wk-group.onoff.site-mall')
                    && !empty(config('wk-core.class.site-mall.site'))
                    && $data['host_type'] == config('wk-core.class.site-mall.site')
                ) {
                    $result = DB::table(config('wk-core.table.site-mall.sites'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));

                }
            }
            if (isset($data['identifier'])) {
                $result = config('wk-core.class.group.group')::where('identifier', $data['identifier'])
                                ->when(isset($data['host_type']), function ($query) use ($data) {
                                    return $query->where('host_type', $data['host_type']);
                                  })
                                ->when(isset($data['host_id']), function ($query) use ($data) {
                                    return $query->where('host_id', $data['host_id']);
                                  })
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-group::system.identifier')]));
            }
        });
    }
}
