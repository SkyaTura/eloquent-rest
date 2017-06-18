<?php

namespace SkyaTura\EloquentREST\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

trait ValidationTrait
{
    /**
     * Array of validation rules. The key must content the respective method name (separated by comma)
     *
     * @var array
     */
    public $rules;

    /**
     * @param string $method
     * @param array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        if (is_a($parameters[0], Request::class) && !empty($this->rules)) {
            $request = $parameters[0];
            foreach ($this->rules as $methods => $rules)
                if (str_contains($methods, $method))
                    $this->validate($request, $rules);
        }

        return parent::callAction($method, $parameters);
    }

    /**
     * Create the response for when a request fails validation.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  array $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return $this->responseError($errors, 422);
    }

    /**
     * {@inheritdoc}
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return [
            "validation" => $validator->errors()->all(),
        ];
    }
}