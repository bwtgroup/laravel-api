<?php

namespace BwtTeam\LaravelAPI\Requests;

use BwtTeam\LaravelAPI\Response\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class ApiRequest extends FormRequest
{

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, new ApiResponse(new ValidationException($validator), 422));
    }

    /**
     * Get the response for a forbidden operation.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forbiddenResponse()
    {
        return new ApiResponse(new AuthorizationException(), 403);
    }
}
