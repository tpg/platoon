<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ConfigValidator
{
    protected Validator $validator;

    public function __construct(protected readonly array $config)
    {
        $this->validator = ValidatorFacade::make($this->config, $this->rules());
    }

    public function validate(): bool
    {
        return $this->validator->passes();
    }

    public function errors(): array
    {
        return $this->validator->errors()->toArray();
    }

    protected function rules(): array
    {
        return [
            'repo' => [
                'required',
                'string',
            ],
            'targets' => [
                'required',
                'array',
                'min:1',
            ],
            'targets.common.*' => [
                'nullable',
            ],
            'targets.*' => [
                'array:connect,jump,host,port,username,root,php,composer,branch,migrate,assets,hooks,extra',
                'min:1',
            ],
            'targets.*.*' => Rule::forEach(function ($value, $attribute) {
                return $this->targetValidationRules($attribute);
            }),
        ];
    }

    protected function targetValidationRules(string $key): array
    {
        $prop = Str::of($key)->after('targets.')->after('.')->toString();

        return $this->targetRule($prop, $this->isCommon($key));
    }

    protected function targetRule(string $key, bool $isCommon = false): array
    {
        return Arr::get([
            'connect' => [
                'sometimes',
                'string',
            ],
            'jump' => [
                'sometimes',
                'array:host,port,username',
            ],
            'jump.host' => [
                'sometimes',
                'string',
            ],
            'jump.port' => [
                'sometimes',
                'integer',
            ],
            'jump.username' => [
                'sometimes',
                'string',
            ],
            'host' => [
                'sometimes',
                'string',
            ],
            'port' => [
                'sometimes',
                'integer',
            ],
            'username' => [
                $isCommon ? 'sometimes' : 'required',
                'string',
            ],
            'root' => [
                $isCommon ? 'sometimes' : 'required',
                'string',
            ],
            'php' => [
                'sometimes',
                'string',
            ],
            'composer' => [
                'sometimes',
                'string',
            ],
            'branch' => [
                'required',
                'string',
            ],
            'migrate' => [
                'sometimes',
                'boolean',
            ],
            'assets' => [
                'sometimes',
                'array',
            ],
            'hooks' => [
                'sometimes',
                'array',
            ],
            'hooks.*' => [
                'sometimes',
                'array'
            ],
            'extra' => [
                'sometimes',
                'array',
            ]
        ], $key);
    }

    protected function isCommon(string $key): bool
    {
        return Str::of($key)->after('.')->startsWith('common');
    }
}
